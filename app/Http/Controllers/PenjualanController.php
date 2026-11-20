<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Services\DocumentNumberGenerator;
use App\Services\JournalPostingService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    protected StockService $stockService;
    protected JournalPostingService $journalService;

    public function __construct(StockService $stockService, JournalPostingService $journalService)
    {
        $this->stockService = $stockService;
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $query = Penjualan::with(['pelanggan', 'user', 'verifier']);

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('id_penjualan', 'LIKE', "%{$s}%")
                    ->orWhereHas('pelanggan', fn ($sub) => $sub->where('nama', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }

        if ($request->filled('metode')) {
            $query->where('metode_pembayaran', $request->metode);
        }

        if ($request->filled('mulai') && $request->filled('selesai')) {
            $query->periode($request->mulai, $request->selesai);
        }

        $penjualan = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($penjualan);
        }

        $pelanggans = Pelanggan::orderBy('nama')->get();
        $barangs = Barang::orderBy('nama_barang')->get();

        return view('transaksi.penjualan.index', compact('penjualan', 'pelanggans', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'id_pelanggan' => 'required|string|exists:pelanggan,id_pelanggan',
            'metode_pembayaran' => 'required|in:Tunai,Kredit',
            'jatuh_tempo' => 'nullable|required_if:metode_pembayaran,Kredit|date|after_or_equal:tanggal',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string|exists:barang,kode_barang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $total += $item['jumlah'] * $item['harga_satuan'];
        }

        if ($request->metode_pembayaran === 'Kredit') {
            $pelanggan = Pelanggan::findOrFail($request->id_pelanggan);
            $totalPiutangBerjalan = $pelanggan->total_piutang;

            if (($totalPiutangBerjalan + $total) > $pelanggan->limit_piutang) {
                $sisaPlafon = max(0, $pelanggan->limit_piutang - $totalPiutangBerjalan);
                $message = "Limit piutang pelanggan {$pelanggan->nama} terlampaui. "
                    . 'Total piutang berjalan: Rp ' . number_format($totalPiutangBerjalan)
                    . ', Limit: Rp ' . number_format($pelanggan->limit_piutang)
                    . ', Sisa plafon: Rp ' . number_format($sisaPlafon);

                if ($request->wantsJson()) {
                    return response()->json(['message' => $message], 422);
                }

                return back()->withInput()->with('error', $message);
            }
        }

        try {
            $penjualan = DB::transaction(function () use ($request, $total) {
                foreach ($request->items as $item) {
                    $barang = Barang::lockForUpdate()->findOrFail($item['kode_barang']);

                    if ($barang->jumlah_barang < $item['jumlah']) {
                        throw new Exception("Stok untuk barang {$barang->nama_barang} tidak mencukupi (Tersedia: {$barang->jumlah_barang}, Diminta: {$item['jumlah']}).");
                    }
                }

                $idPenjualan = DocumentNumberGenerator::generate('PJ', 'penjualan', 'id_penjualan', $request->tanggal);

                $penjualan = Penjualan::create([
                    'id_penjualan' => $idPenjualan,
                    'tanggal' => $request->tanggal,
                    'id_pelanggan' => $request->id_pelanggan,
                    'id_user' => Auth::user()->id_user ?? Auth::id(),
                    'total' => $total,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'jatuh_tempo' => $request->metode_pembayaran === 'Kredit' ? $request->jatuh_tempo : null,
                    'status_verifikasi' => 'Menunggu',
                ]);

                foreach ($request->items as $item) {
                    DetailPenjualan::create([
                        'id_penjualan' => $idPenjualan,
                        'kode_barang' => $item['kode_barang'],
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $item['harga_satuan'],
                        'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                    ]);
                }

                return $penjualan;
            });
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Penjualan berhasil disimpan dan menunggu verifikasi', 'data' => $penjualan->load('details')], 201);
        }

        return redirect()->route('penjualan.index')->with('success', "Penjualan {$penjualan->id_penjualan} berhasil dibuat.");
    }

    public function show(string $id_penjualan)
    {
        $penjualan = Penjualan::with(['pelanggan', 'user', 'verifier', 'details.barang', 'juPenjualan.coa'])->findOrFail($id_penjualan);

        return response()->json($penjualan);
    }

    /**
     * Verifikasi Penjualan: Kurangi stok, catat kartu stok, dan posting jurnal umum
     */
    public function verify(Request $request, string $id_penjualan)
    {
        $penjualan = Penjualan::with(['details.barang', 'pelanggan'])->findOrFail($id_penjualan);
        $currentUser = Auth::user();

        if ($penjualan->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Faktur Penjualan {$id_penjualan} sudah terverifikasi.");
        }

        if ($penjualan->id_user === ($currentUser->id_user ?? Auth::id()) && ! $currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        try {
            DB::transaction(function () use ($penjualan, $currentUser) {
                $totalHpp = 0;

                foreach ($penjualan->details as $item) {
                    $subHpp = (int) round($item->jumlah * (float) $item->barang->hpp);
                    $totalHpp += $subHpp;

                    $this->stockService->recordKeluar(
                        $item->kode_barang,
                        $item->jumlah,
                        (float) $item->harga_satuan,
                        'Penjualan',
                        $penjualan->id_penjualan,
                        "Pengiriman barang ke {$penjualan->pelanggan->nama}"
                    );
                }

                $debitAkun = $penjualan->metode_pembayaran === 'Tunai' ? 1101 : 1103;
                $ketDebit = $penjualan->metode_pembayaran === 'Tunai'
                    ? "Penerimaan kas atas penjualan tunai faktur {$penjualan->id_penjualan}"
                    : "Piutang usaha atas penjualan kredit faktur {$penjualan->id_penjualan}";

                $entries = [
                    [
                        'kode_akun' => $debitAkun,
                        'debit' => (int) round((float) $penjualan->total),
                        'kredit' => 0,
                        'keterangan' => $ketDebit,
                    ],
                    [
                        'kode_akun' => 4101,
                        'debit' => 0,
                        'kredit' => (int) round((float) $penjualan->total),
                        'keterangan' => "Pendapatan penjualan atas faktur {$penjualan->id_penjualan}",
                    ],
                ];

                if ($totalHpp > 0) {
                    $entries[] = [
                        'kode_akun' => 5101,
                        'debit' => $totalHpp,
                        'kredit' => 0,
                        'keterangan' => "Beban Pokok Penjualan atas faktur {$penjualan->id_penjualan}",
                    ];
                    $entries[] = [
                        'kode_akun' => 1104,
                        'debit' => 0,
                        'kredit' => $totalHpp,
                        'keterangan' => "Pengurangan persediaan barang atas faktur {$penjualan->id_penjualan}",
                    ];
                }

                $this->journalService->post(
                    'ju_penjualan',
                    'id_penjualan',
                    $penjualan->id_penjualan,
                    $penjualan->tanggal->toDateString(),
                    $entries
                );

                $penjualan->status_verifikasi = 'Terverifikasi';
                $penjualan->verified_by = $currentUser->id_user ?? Auth::id();
                $penjualan->verified_at = now();
                $penjualan->save();
            });
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => "Penjualan {$id_penjualan} berhasil diverifikasi dan jurnal diposting."]);
        }

        return redirect()->route('penjualan.index')->with('success', "Penjualan {$id_penjualan} berhasil diverifikasi.");
    }
}
