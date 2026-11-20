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

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('no_pelunasan', 'LIKE', "%{$s}%")
                ->orWhere('id_penjualan', 'LIKE', "%{$s}%");
        }

        $pelunasans = $query->orderBy('tanggal', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($pelunasans);
        }

        return view('transaksi.pelunasan_piutang.index', compact('pelunasans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_penjualan' => 'required|string|exists:penjualan,id_penjualan',
            'tanggal' => 'required|date',
            'total' => 'required|numeric|min:1',
            'metode_bayar' => 'nullable|in:Kas,Bank BCA',
            'catatan' => 'nullable|string',
        ]);

        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        if ($penjualan->sisa_piutang < $request->total) {
            return back()->with('error', "Nominal pelunasan (Rp " . number_format($request->total) . ") melebihi sisa piutang pelanggan (Rp " . number_format($penjualan->sisa_piutang) . ").");
        }

        $noPelunasan = DocumentNumberGenerator::generate('PP', 'pelunasan_piutang', 'no_pelunasan');

        $pelunasan = PelunasanPiutang::create([
            'no_pelunasan' => $noPelunasan,
            'id_penjualan' => $request->id_penjualan,
            'tanggal' => $request->tanggal,
            'total' => $request->total,
            'id_user' => Auth::user()->id_user ?? Auth::id(),
            'status_verifikasi' => 'Menunggu',
            'catatan' => $request->catatan . ($request->metode_bayar ? " [Penerimaan via: {$request->metode_bayar}]" : ''),
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

        if ($pelunasan->id_user === ($currentUser->id_user ?? Auth::id()) && !$currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        DB::transaction(function () use ($pelunasan, $currentUser) {
            // Tentukan akun debit (Bank BCA 1102 atau Kas 1101)
            $isBca = str_contains($pelunasan->catatan ?? '', 'Bank BCA');
            $debitAkun = $isBca ? 1102 : 1101;
            $namaAkun = $isBca ? 'Bank BCA' : 'Kas di Tangan';

            // Jurnal Pelunasan Piutang:
            // (D) Kas/Bank = Total
            // (K) Piutang Usaha (1103) = Total
            $entries = [
                [
                    'kode_akun' => $debitAkun,
                    'debit' => (int) $pelunasan->total,
                    'kredit' => 0,
                    'keterangan' => "Penerimaan {$namaAkun} atas pelunasan piutang faktur {$pelunasan->id_penjualan}",
                ],
                [
                    'kode_akun' => 1103,
                    'debit' => 0,
                    'kredit' => (int) $pelunasan->total,
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

            // Update status
            $pelunasan->status_verifikasi = 'Terverifikasi';
            $pelunasan->verified_by = $currentUser->id_user ?? Auth::id();
            $pelunasan->verified_at = now();
            $pelunasan->save();
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => "Pelunasan Piutang {$no_pelunasan} berhasil diverifikasi."]);
        }

        return redirect()->route('pelunasan-piutang.index')->with('success', "Pelunasan Piutang {$no_pelunasan} berhasil diverifikasi.");
    }
}
