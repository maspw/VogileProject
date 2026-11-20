<?php

namespace App\Http\Controllers;

use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Services\DocumentNumberGenerator;
use App\Services\JournalPostingService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
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
        $query = Pembelian::with(['supplier', 'user', 'verifier']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('nota_pembelian', 'LIKE', "%{$s}%")
                ->orWhereHas('supplier', function ($q) use ($s) {
                    $q->where('nama_supplier', 'LIKE', "%{$s}%");
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

        $pembelian = $query->orderBy('tanggal', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($pembelian);
        }

        return view('transaksi.pembelian.index', compact('pembelian'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'id_supplier' => 'required|string|exists:supplier,id_supplier',
            'metode_pembayaran' => 'required|in:Tunai,Kredit',
            'jatuh_tempo' => 'nullable|required_if:metode_pembayaran,Kredit|date|after_or_equal:tanggal',
            'no_po' => 'nullable|string|exists:purchase_order,no_po',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string|exists:barang,kode_barang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $nota = DocumentNumberGenerator::generate('PB', 'pembelian', 'nota_pembelian');
            $total = 0;

            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga_satuan'];
            }

            $pembelian = Pembelian::create([
                'nota_pembelian' => $nota,
                'tanggal' => $request->tanggal,
                'id_supplier' => $request->id_supplier,
                'id_user' => Auth::user()->id_user ?? Auth::id(),
                'total' => $total,
                'metode_pembayaran' => $request->metode_pembayaran,
                'jatuh_tempo' => $request->metode_pembayaran === 'Kredit' ? $request->jatuh_tempo : null,
                'status_verifikasi' => 'Menunggu',
                'no_po' => $request->no_po,
            ]);

            foreach ($request->items as $item) {
                DetailPembelian::create([
                    'nota_pembelian' => $nota,
                    'kode_barang' => $item['kode_barang'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Pembelian berhasil dicatat dan menunggu verifikasi', 'data' => $pembelian->load('details')], 201);
            }

            return redirect()->route('pembelian.index')->with('success', "Nota Pembelian {$nota} berhasil dicatat.");
        });
    }

    public function show(string $nota_pembelian)
    {
        $pembelian = Pembelian::with(['supplier', 'user', 'verifier', 'details.barang', 'juPembelian.coa'])->findOrFail($nota_pembelian);
        return response()->json($pembelian);
    }

    /**
     * Verifikasi Pembelian: Update stok barang, catat kartu stok, dan posting jurnal umum
     */
    public function verify(Request $request, string $nota_pembelian)
    {
        $pembelian = Pembelian::with('details')->findOrFail($nota_pembelian);
        $currentUser = Auth::user();

        if ($pembelian->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Nota Pembelian {$nota_pembelian} sudah terverifikasi sebelumnya.");
        }

        // Aturan Segregation of Duties: Penginput dilarang memverifikasi transaksinya sendiri
        if ($pembelian->id_user === ($currentUser->id_user ?? Auth::id()) && !$currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        DB::transaction(function () use ($pembelian, $currentUser) {
            // 1. Mutasi Stok Masuk & Kartu Stok
            foreach ($pembelian->details as $item) {
                $this->stockService->recordMasuk(
                    $item->kode_barang,
                    $item->jumlah,
                    (float) $item->harga_satuan,
                    'Pembelian',
                    $pembelian->nota_pembelian,
                    "Penerimaan barang dari {$pembelian->supplier->nama_supplier}"
                );
            }

            // 2. Posting Jurnal Umum
            // (D) Persediaan Barang Dagang (1104) = Total
            // (K) Kas (1101) jika Tunai, atau Hutang Usaha (2101) jika Kredit
            $kreditAkun = $pembelian->metode_pembayaran === 'Tunai' ? 1101 : 2101;
            $keteranganKredit = $pembelian->metode_pembayaran === 'Tunai'
                ? "Pengeluaran kas pembelian tunai nota {$pembelian->nota_pembelian}"
                : "Hutang usaha atas pembelian nota {$pembelian->nota_pembelian}";

            $entries = [
                [
                    'kode_akun' => 1104,
                    'debit' => (int) $pembelian->total,
                    'kredit' => 0,
                    'keterangan' => "Penambahan persediaan atas nota {$pembelian->nota_pembelian}",
                ],
                [
                    'kode_akun' => $kreditAkun,
                    'debit' => 0,
                    'kredit' => (int) $pembelian->total,
                    'keterangan' => $keteranganKredit,
                ],
            ];

            $this->journalService->post(
                'ju_pembelian',
                'nota_pembelian',
                $pembelian->nota_pembelian,
                $pembelian->tanggal->toDateString(),
                $entries
            );

            // 3. Update status verifikasi
            $pembelian->status_verifikasi = 'Terverifikasi';
            $pembelian->verified_by = $currentUser->id_user ?? Auth::id();
            $pembelian->verified_at = now();
            $pembelian->save();
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => "Pembelian {$nota_pembelian} berhasil diverifikasi dan jurnal diposting."]);
        }

        return redirect()->route('pembelian.index')->with('success', "Pembelian {$nota_pembelian} berhasil diverifikasi.");
    }
}
