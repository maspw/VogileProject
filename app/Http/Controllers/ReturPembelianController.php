<?php

namespace App\Http\Controllers;

use App\Models\DetailReturPembelian;
use App\Models\Pembelian;
use App\Models\ReturPembelian;
use App\Services\DocumentNumberGenerator;
use App\Services\JournalPostingService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturPembelianController extends Controller
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
        $query = ReturPembelian::with(['pembelian.supplier', 'user', 'verifier']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('no_retur', 'LIKE', "%{$s}%")
                ->orWhere('nota_pembelian', 'LIKE', "%{$s}%");
        }

        $returs = $query->orderBy('tanggal', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($returs);
        }

        return view('transaksi.retur_pembelian.index', compact('returs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nota_pembelian' => 'required|string|exists:pembelian,nota_pembelian',
            'tanggal' => 'required|date',
            'alasan' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string|exists:barang,kode_barang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $noRetur = DocumentNumberGenerator::generate('RTB', 'retur_pembelian', 'no_retur');
            $total = 0;

            foreach ($request->items as $item) {
                $total += $item['jumlah'] * $item['harga_satuan'];
            }

            $retur = ReturPembelian::create([
                'no_retur' => $noRetur,
                'nota_pembelian' => $request->nota_pembelian,
                'tanggal' => $request->tanggal,
                'alasan' => $request->alasan,
                'total' => $total,
                'id_user' => Auth::user()->id_user ?? Auth::id(),
                'status_verifikasi' => 'Menunggu',
            ]);

            foreach ($request->items as $item) {
                DetailReturPembelian::create([
                    'no_retur' => $noRetur,
                    'kode_barang' => $item['kode_barang'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Retur Pembelian berhasil dicatat', 'data' => $retur->load('details')], 201);
            }

            return redirect()->route('retur-pembelian.index')->with('success', "Retur Pembelian {$noRetur} berhasil dicatat.");
        });
    }

    public function verify(Request $request, string $no_retur)
    {
        $retur = ReturPembelian::with(['details', 'pembelian'])->findOrFail($no_retur);
        $currentUser = Auth::user();

        if ($retur->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Retur Pembelian {$no_retur} sudah terverifikasi.");
        }

        if ($retur->id_user === ($currentUser->id_user ?? Auth::id()) && !$currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        DB::transaction(function () use ($retur, $currentUser) {
            // 1. Kurangi stok barang karena dikembalikan ke supplier
            foreach ($retur->details as $item) {
                $this->stockService->recordKeluar(
                    $item->kode_barang,
                    $item->jumlah,
                    (float) $item->harga_satuan,
                    'Retur Pembelian',
                    $retur->no_retur,
                    "Pengembalian barang ke supplier atas nota {$retur->nota_pembelian}"
                );
            }

            // 2. Jurnal Retur Pembelian:
            // (D) Hutang Usaha (2101) jika kredit, atau Kas (1101) jika tunai = Total
            // (K) Persediaan Barang Dagang (1104) = Total
            $debitAkun = $retur->pembelian->metode_pembayaran === 'Kredit' ? 2101 : 1101;
            $ketDebit = $retur->pembelian->metode_pembayaran === 'Kredit'
                ? "Pengurangan hutang atas retur pembelian {$retur->no_retur}"
                : "Penerimaan kembali kas atas retur pembelian {$retur->no_retur}";

            $entries = [
                [
                    'kode_akun' => $debitAkun,
                    'debit' => (int) $retur->total,
                    'kredit' => 0,
                    'keterangan' => $ketDebit,
                ],
                [
                    'kode_akun' => 1104,
                    'debit' => 0,
                    'kredit' => (int) $retur->total,
                    'keterangan' => "Pengurangan persediaan atas retur pembelian {$retur->no_retur}",
                ],
            ];

            $this->journalService->post(
                'ju_retur_pemb',
                'no_retur',
                $retur->no_retur,
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
            return response()->json(['message' => "Retur Pembelian {$no_retur} berhasil diverifikasi."]);
        }

        return redirect()->route('retur-pembelian.index')->with('success', "Retur Pembelian {$no_retur} berhasil diverifikasi.");
    }
}
