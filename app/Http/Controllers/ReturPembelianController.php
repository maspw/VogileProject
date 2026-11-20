<?php

namespace App\Http\Controllers;

use App\Models\Barang;
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

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('no_retur', 'LIKE', "%{$s}%")
                    ->orWhere('nota_pembelian', 'LIKE', "%{$s}%");
            });
        }

        $returs = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($returs);
        }

        $pembelians = Pembelian::with('supplier')
            ->where('status_verifikasi', 'Terverifikasi')
            ->orderBy('tanggal', 'desc')
            ->get();
        $barangs = Barang::orderBy('nama_barang')->get();

        return view('transaksi.retur_pembelian.index', compact('returs', 'pembelians', 'barangs'));
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

        $pembelian = Pembelian::with('details')->findOrFail($request->nota_pembelian);

        if ($pembelian->status_verifikasi !== 'Terverifikasi') {
            return back()->withInput()->with('error', "Nota {$pembelian->nota_pembelian} belum terverifikasi sehingga belum dapat diretur.");
        }

        $dibeli = $pembelian->details->groupBy('kode_barang')->map(fn ($rows) => (int) $rows->sum('jumlah'));

        $sudahRetur = DB::table('detail_retur_pembelian as d')
            ->join('retur_pembelian as r', 'r.no_retur', '=', 'd.no_retur')
            ->where('r.nota_pembelian', $pembelian->nota_pembelian)
            ->where('r.status_verifikasi', '!=', 'Ditolak')
            ->select('d.kode_barang', DB::raw('SUM(d.jumlah) as qty'))
            ->groupBy('d.kode_barang')
            ->pluck('qty', 'd.kode_barang');

        $diminta = [];
        foreach ($request->items as $item) {
            $diminta[$item['kode_barang']] = ($diminta[$item['kode_barang']] ?? 0) + (int) $item['jumlah'];
        }

        foreach ($diminta as $kode => $qty) {
            $sisa = (int) ($dibeli[$kode] ?? 0) - (int) ($sudahRetur[$kode] ?? 0);

            if ($sisa <= 0) {
                return back()->withInput()->with('error', "Barang {$kode} tidak terdapat pada nota {$pembelian->nota_pembelian} atau sudah diretur seluruhnya.");
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
                $noRetur = DocumentNumberGenerator::generate('RTB', 'retur_pembelian', 'no_retur', $request->tanggal);

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

                return $retur;
            });
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Retur Pembelian berhasil dicatat', 'data' => $retur->load('details')], 201);
        }

        return redirect()->route('retur-pembelian.index')->with('success', "Retur Pembelian {$retur->no_retur} berhasil dicatat.");
    }

    public function verify(Request $request, string $no_retur)
    {
        $retur = ReturPembelian::with(['details', 'pembelian'])->findOrFail($no_retur);
        $currentUser = Auth::user();

        if ($retur->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Retur Pembelian {$no_retur} sudah terverifikasi.");
        }

        if ($retur->id_user === ($currentUser->id_user ?? Auth::id()) && ! $currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        try {
            DB::transaction(function () use ($retur, $currentUser) {
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

                $debitAkun = $retur->pembelian->metode_pembayaran === 'Kredit' ? 2101 : 1101;
                $ketDebit = $retur->pembelian->metode_pembayaran === 'Kredit'
                    ? "Pengurangan hutang atas retur pembelian {$retur->no_retur}"
                    : "Penerimaan kembali kas atas retur pembelian {$retur->no_retur}";

                $totalRetur = (int) round((float) $retur->total);

                $entries = [
                    [
                        'kode_akun' => $debitAkun,
                        'debit' => $totalRetur,
                        'kredit' => 0,
                        'keterangan' => $ketDebit,
                    ],
                    [
                        'kode_akun' => 1104,
                        'debit' => 0,
                        'kredit' => $totalRetur,
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
            return response()->json(['message' => "Retur Pembelian {$no_retur} berhasil diverifikasi."]);
        }

        return redirect()->route('retur-pembelian.index')->with('success', "Retur Pembelian {$no_retur} berhasil diverifikasi.");
    }
}
