<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailReturPenjualan;
use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use App\Services\DocumentNumberGenerator;
use App\Services\JournalPostingService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturPenjualanController extends Controller
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
        $query = ReturPenjualan::with(['penjualan.pelanggan', 'user', 'verifier']);

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('no_retur_penjualan', 'LIKE', "%{$s}%")
                    ->orWhere('id_penjualan', 'LIKE', "%{$s}%");
            });
        }

        $returs = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($returs);
        }

        $penjualans = Penjualan::with('pelanggan')
            ->where('status_verifikasi', 'Terverifikasi')
            ->orderBy('tanggal', 'desc')
            ->get();
        $barangs = Barang::orderBy('nama_barang')->get();

        return view('transaksi.retur_penjualan.index', compact('returs', 'penjualans', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_penjualan' => 'required|string|exists:penjualan,id_penjualan',
            'tanggal' => 'required|date',
            'alasan' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string|exists:barang,kode_barang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $penjualan = Penjualan::with('details')->findOrFail($request->id_penjualan);

        if ($penjualan->status_verifikasi !== 'Terverifikasi') {
            return back()->withInput()->with('error', "Faktur {$penjualan->id_penjualan} belum terverifikasi sehingga belum dapat diretur.");
        }

        $terjual = $penjualan->details->groupBy('kode_barang')->map(fn ($rows) => (int) $rows->sum('jumlah'));

        $sudahRetur = DB::table('detail_retur_penjualan as d')
            ->join('retur_penjualan as r', 'r.no_retur_penjualan', '=', 'd.no_retur_penjualan')
            ->where('r.id_penjualan', $penjualan->id_penjualan)
            ->where('r.status_verifikasi', '!=', 'Ditolak')
            ->select('d.kode_barang', DB::raw('SUM(d.jumlah) as qty'))
            ->groupBy('d.kode_barang')
            ->pluck('qty', 'd.kode_barang');

        $diminta = [];
        foreach ($request->items as $item) {
            $diminta[$item['kode_barang']] = ($diminta[$item['kode_barang']] ?? 0) + (int) $item['jumlah'];
        }

        foreach ($diminta as $kode => $qty) {
            $sisa = (int) ($terjual[$kode] ?? 0) - (int) ($sudahRetur[$kode] ?? 0);

            if ($sisa <= 0) {
                return back()->withInput()->with('error', "Barang {$kode} tidak terdapat pada faktur {$penjualan->id_penjualan} atau sudah diretur seluruhnya.");
            }

            if ($qty > $sisa) {
                return back()->withInput()->with('error', "Jumlah retur barang {$kode} melebihi sisa yang dapat diretur ({$sisa}).");
            }
        }

        $total = 0;
        foreach ($request->items as $item) {
            $total += $item['jumlah'] * $item['harga_satuan'];
        }

        try {
            $retur = DB::transaction(function () use ($request, $total) {
                $noRetur = DocumentNumberGenerator::generate('RTJ', 'retur_penjualan', 'no_retur_penjualan', $request->tanggal);

                $retur = ReturPenjualan::create([
                    'no_retur_penjualan' => $noRetur,
                    'id_penjualan' => $request->id_penjualan,
                    'tanggal' => $request->tanggal,
                    'alasan' => $request->alasan,
                    'total' => $total,
                    'id_user' => Auth::user()->id_user ?? Auth::id(),
                    'status_verifikasi' => 'Menunggu',
                ]);

                foreach ($request->items as $item) {
                    DetailReturPenjualan::create([
                        'no_retur_penjualan' => $noRetur,
                        'kode_barang' => $item['kode_barang'],
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $item['harga_satuan'],
                        'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                    ]);
                }

                return $retur;
            });
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Retur Penjualan berhasil dicatat', 'data' => $retur->load('details')], 201);
        }

        return redirect()->route('retur-penjualan.index')->with('success', "Retur Penjualan {$retur->no_retur_penjualan} berhasil dicatat.");
    }

    public function verify(Request $request, string $no_retur_penjualan)
    {
        $retur = ReturPenjualan::with(['details.barang', 'penjualan'])->findOrFail($no_retur_penjualan);
        $currentUser = Auth::user();

        if ($retur->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Retur Penjualan {$no_retur_penjualan} sudah terverifikasi.");
        }

        if ($retur->id_user === ($currentUser->id_user ?? Auth::id()) && ! $currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        try {
            DB::transaction(function () use ($retur, $currentUser) {
                $totalHpp = 0;

                foreach ($retur->details as $item) {
                    $subHpp = (int) round($item->jumlah * (float) $item->barang->hpp);
                    $totalHpp += $subHpp;

                    $this->stockService->recordMasuk(
                        $item->kode_barang,
                        $item->jumlah,
                        (float) $item->harga_satuan,
                        'Retur Penjualan',
                        $retur->no_retur_penjualan,
                        "Penerimaan kembali barang retur dari pelanggan faktur {$retur->id_penjualan}"
                    );
                }

                $kreditAkun = $retur->penjualan->metode_pembayaran === 'Kredit' ? 1103 : 1101;
                $ketKredit = $retur->penjualan->metode_pembayaran === 'Kredit'
                    ? "Pengurangan piutang atas retur penjualan {$retur->no_retur_penjualan}"
                    : "Pengeluaran kas pengembalian uang retur {$retur->no_retur_penjualan}";

                $totalRetur = (int) round((float) $retur->total);

                $entries = [
                    [
                        'kode_akun' => 4102,
                        'debit' => $totalRetur,
                        'kredit' => 0,
                        'keterangan' => "Pencatatan retur penjualan {$retur->no_retur_penjualan}",
                    ],
                    [
                        'kode_akun' => $kreditAkun,
                        'debit' => 0,
                        'kredit' => $totalRetur,
                        'keterangan' => $ketKredit,
                    ],
                ];

                if ($totalHpp > 0) {
                    $entries[] = [
                        'kode_akun' => 1104,
                        'debit' => $totalHpp,
                        'kredit' => 0,
                        'keterangan' => "Pengembalian persediaan dari retur {$retur->no_retur_penjualan}",
                    ];
                    $entries[] = [
                        'kode_akun' => 5101,
                        'debit' => 0,
                        'kredit' => $totalHpp,
                        'keterangan' => "Pengurangan HPP atas barang retur {$retur->no_retur_penjualan}",
                    ];
                }

                $this->journalService->post(
                    'ju_retur_penj',
                    'no_retur_penjualan',
                    $retur->no_retur_penjualan,
                    $retur->tanggal->toDateString(),
                    $entries
                );

                $retur->status_verifikasi = 'Terverifikasi';
                $retur->verified_by = $currentUser->id_user ?? Auth::id();
                $retur->verified_at = now();
                $retur->save();
            });
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => "Retur Penjualan {$no_retur_penjualan} berhasil diverifikasi."]);
        }

        return redirect()->route('retur-penjualan.index')->with('success', "Retur Penjualan {$no_retur_penjualan} berhasil diverifikasi.");
    }
}
