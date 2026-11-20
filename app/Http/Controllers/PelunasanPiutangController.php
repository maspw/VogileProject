<?php

namespace App\Http\Controllers;

use App\Models\PelunasanPiutang;
use App\Models\Penjualan;
use App\Services\DocumentNumberGenerator;
use App\Services\JournalPostingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelunasanPiutangController extends Controller
{
    protected JournalPostingService $journalService;

    public function __construct(JournalPostingService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $query = PelunasanPiutang::with(['penjualan.pelanggan', 'user', 'verifier']);

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('no_pelunasan', 'LIKE', "%{$s}%")
                    ->orWhere('id_penjualan', 'LIKE', "%{$s}%");
            });
        }

        $pelunasans = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($pelunasans);
        }

        $penjualans = Penjualan::with('pelanggan')
            ->where('status_verifikasi', 'Terverifikasi')
            ->where('metode_pembayaran', 'Kredit')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->filter(fn ($p) => $p->sisa_piutang_tersedia > 0)
            ->values();

        return view('transaksi.pelunasan_piutang.index', compact('pelunasans', 'penjualans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_penjualan' => 'required|string|exists:penjualan,id_penjualan',
            'tanggal' => 'required|date',
            'total' => 'required|numeric|min:1',
            'metode_bayar' => 'required|in:Kas,Bank BCA',
            'catatan' => 'nullable|string',
        ]);

        $penjualan = Penjualan::findOrFail($request->id_penjualan);

        if ($penjualan->status_verifikasi !== 'Terverifikasi') {
            return back()->withInput()->with('error', "Faktur {$penjualan->id_penjualan} belum terverifikasi sehingga belum dapat dilunasi.");
        }

        if ($penjualan->metode_pembayaran !== 'Kredit') {
            return back()->withInput()->with('error', "Faktur {$penjualan->id_penjualan} adalah penjualan tunai dan tidak memiliki piutang.");
        }

        if ((float) $request->total > $penjualan->sisa_piutang_tersedia) {
            return back()->withInput()->with('error', 'Nominal pelunasan (Rp ' . number_format($request->total) . ') melebihi sisa piutang yang tersedia (Rp ' . number_format($penjualan->sisa_piutang_tersedia) . ').');
        }

        $noPelunasan = DocumentNumberGenerator::generate('PP', 'pelunasan_piutang', 'no_pelunasan', $request->tanggal);

        $pelunasan = PelunasanPiutang::create([
            'no_pelunasan' => $noPelunasan,
            'id_penjualan' => $request->id_penjualan,
            'tanggal' => $request->tanggal,
            'total' => $request->total,
            'metode_bayar' => $request->metode_bayar,
            'id_user' => Auth::user()->id_user ?? Auth::id(),
            'status_verifikasi' => 'Menunggu',
            'catatan' => $request->catatan,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pelunasan piutang berhasil dicatat', 'data' => $pelunasan], 201);
        }

        return redirect()->route('pelunasan-piutang.index')->with('success', "Pelunasan Piutang {$noPelunasan} berhasil dicatat.");
    }

    public function verify(Request $request, string $no_pelunasan)
    {
        $pelunasan = PelunasanPiutang::with('penjualan.pelanggan')->findOrFail($no_pelunasan);
        $currentUser = Auth::user();

        if ($pelunasan->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Pelunasan Piutang {$no_pelunasan} sudah terverifikasi.");
        }

        if ($pelunasan->id_user === ($currentUser->id_user ?? Auth::id()) && ! $currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        $penjualan = $pelunasan->penjualan;
        $totalTercatat = (float) $penjualan->pelunasanPiutang()
            ->where('no_pelunasan', '!=', $no_pelunasan)
            ->where('status_verifikasi', '!=', 'Ditolak')
            ->sum('total')
            + (float) $penjualan->retur()
                ->where('status_verifikasi', '!=', 'Ditolak')
                ->sum('total');

        if ($totalTercatat + (float) $pelunasan->total > (float) $penjualan->total) {
            $message = "Total pelunasan melebihi nilai faktur {$penjualan->id_penjualan}. Periksa pelunasan lain yang masih menunggu verifikasi.";

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        try {
            DB::transaction(function () use ($pelunasan, $currentUser) {
                $isBca = $pelunasan->metode_bayar === 'Bank BCA';
                $debitAkun = $isBca ? 1102 : 1101;
                $namaAkun = $isBca ? 'Bank BCA' : 'Kas di Tangan';
                $total = (int) round((float) $pelunasan->total);

                $entries = [
                    [
                        'kode_akun' => $debitAkun,
                        'debit' => $total,
                        'kredit' => 0,
                        'keterangan' => "Penerimaan {$namaAkun} atas pelunasan piutang faktur {$pelunasan->id_penjualan}",
                    ],
                    [
                        'kode_akun' => 1103,
                        'debit' => 0,
                        'kredit' => $total,
                        'keterangan' => "Pengurangan piutang usaha pelanggan {$pelunasan->penjualan->pelanggan->nama}",
                    ],
                ];

                $this->journalService->post(
                    'ju_piutang',
                    'no_pelunasan',
                    $pelunasan->no_pelunasan,
                    $pelunasan->tanggal->toDateString(),
                    $entries
                );

                $pelunasan->status_verifikasi = 'Terverifikasi';
                $pelunasan->verified_by = $currentUser->id_user ?? Auth::id();
                $pelunasan->verified_at = now();
                $pelunasan->save();
            });
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => "Pelunasan Piutang {$no_pelunasan} berhasil diverifikasi."]);
        }

        return redirect()->route('pelunasan-piutang.index')->with('success', "Pelunasan Piutang {$no_pelunasan} berhasil diverifikasi.");
    }
}
