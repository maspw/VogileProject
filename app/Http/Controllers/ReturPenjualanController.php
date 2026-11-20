<?php

namespace App\Http\Controllers;

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

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('no_retur_penjualan', 'LIKE', "%{$s}%")
                ->orWhere('id_penjualan', 'LIKE', "%{$s}%");
        }

        $returs = $query->orderBy('tanggal', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($returs);
        }

        return view('transaksi.retur_penjualan.index', compact('returs'));
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

        return DB::transaction(function () use ($request) {
            $noRetur = DocumentNumberGenerator::generate('RTJ', 'retur_penjualan', 'no_retur_penjualan');
            $total = 0;

            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga_satuan'];
            }

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

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Retur Penjualan berhasil dicatat', 'data' => $retur->load('details')], 201);
            }

            return redirect()->route('retur-penjualan.index')->with('success', "Retur Penjualan {$noRetur} berhasil dicatat.");
        });
    }

    public function verify(Request $request, string $no_retur_penjualan)
    {
        $retur = ReturPenjualan::with(['details.barang', 'penjualan'])->findOrFail($no_retur_penjualan);
        $currentUser = Auth::user();

        if ($retur->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Retur Penjualan {$no_retur_penjualan} sudah terverifikasi.");
        }

        if ($retur->id_user === ($currentUser->id_user ?? Auth::id()) && !$currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        DB::transaction(function () use ($retur, $currentUser) {
            $totalHpp = 0;

            // 1. Kembalikan stok ke gudang
            foreach ($retur->details as $item) {
                $barangHpp = (float) $item->barang->hpp;
                $subHpp = (int) ($item->jumlah * $barangHpp);
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

            // 2. Jurnal Retur Penjualan:
            // Debit: Retur Penjualan (4102) = Total
            // Kredit: Piutang Usaha (1103) jika kredit, atau Kas (1101) jika tunai = Total
            // Debit: Persediaan (1104) = Total HPP
            // Kredit: HPP (5101) = Total HPP
            $kreditAkun = $retur->penjualan->metode_pembayaran === 'Kredit' ? 1103 : 1101;
            $ketKredit = $retur->penjualan->metode_pembayaran === 'Kredit'
                ? "Pengurangan piutang atas retur penjualan {$retur->no_retur_penjualan}"
                : "Pengeluaran kas pengembalian uang retur {$retur->no_retur_penjualan}";

            $entries = [
                [
                    'kode_akun' => 4102,
                    'debit' => (int) $retur->total,
                    'kredit' => 0,
                    'keterangan' => "Pencatatan retur penjualan {$retur->no_retur_penjualan}",
                ],
                [
                    'kode_akun' => $kreditAkun,
                    'debit' => 0,
                    'kredit' => (int) $retur->total,
                    'keterangan' => $ketKredit,
                ],
            ];

            if ($totalHpp > 0) {
                $entries[] = [
                    'kode_akun' => 1104,
                    'debit' => (int) $totalHpp,
                    'kredit' => 0,
                    'keterangan' => "Pengembalian persediaan dari retur {$retur->no_retur_penjualan}",
                ];
                $entries[] = [
                    'kode_akun' => 5101,
                    'debit' => 0,
                    'kredit' => (int) $totalHpp,
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

            // 3. Update status
            $retur->status_verifikasi = 'Terverifikasi';
            $retur->verified_by = $currentUser->id_user ?? Auth::id();
            $retur->verified_at = now();
            $retur->save();
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => "Retur Penjualan {$no_retur_penjualan} berhasil diverifikasi."]);
        }

        return redirect()->route('retur-penjualan.index')->with('success', "Retur Penjualan {$no_retur_penjualan} berhasil diverifikasi.");
    }
}
