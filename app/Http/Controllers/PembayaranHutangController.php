<?php

namespace App\Http\Controllers;

use App\Models\PembayaranHutang;
use App\Models\Pembelian;
use App\Services\DocumentNumberGenerator;
use App\Services\JournalPostingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranHutangController extends Controller
{
    protected JournalPostingService $journalService;

    public function __construct(JournalPostingService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $query = PembayaranHutang::with(['pembelian.supplier', 'user', 'verifier']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('no_pembayaran', 'LIKE', "%{$s}%")
                ->orWhere('nota_pembelian', 'LIKE', "%{$s}%");
        }

        $pembayaran = $query->orderBy('tanggal', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($pembayaran);
        }

        return view('transaksi.pembayaran_hutang.index', compact('pembayaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nota_pembelian' => 'required|string|exists:pembelian,nota_pembelian',
            'tanggal' => 'required|date',
            'total' => 'required|numeric|min:1',
            'metode_bayar' => 'nullable|in:Kas,Bank BCA',
            'catatan' => 'nullable|string',
        ]);

        $pembelian = Pembelian::findOrFail($request->nota_pembelian);
        if ($pembelian->sisa_hutang < $request->total) {
            return back()->with('error', "Nominal pembayaran (Rp " . number_format($request->total) . ") melebihi sisa hutang (Rp " . number_format($pembelian->sisa_hutang) . ").");
        }

        $noPembayaran = DocumentNumberGenerator::generate('PH', 'pembayaran_hutang', 'no_pembayaran');

        $pembayaran = PembayaranHutang::create([
            'no_pembayaran' => $noPembayaran,
            'nota_pembelian' => $request->nota_pembelian,
            'tanggal' => $request->tanggal,
            'total' => $request->total,
            'id_user' => Auth::user()->id_user ?? Auth::id(),
            'status_verifikasi' => 'Menunggu',
            'catatan' => $request->catatan . ($request->metode_bayar ? " [Metode: {$request->metode_bayar}]" : ''),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pembayaran hutang berhasil dicatat', 'data' => $pembayaran], 201);
        }

        return redirect()->route('pembayaran-hutang.index')->with('success', "Pembayaran Hutang {$noPembayaran} berhasil dicatat.");
    }

    public function verify(Request $request, string $no_pembayaran)
    {
        $pembayaran = PembayaranHutang::with('pembelian')->findOrFail($no_pembayaran);
        $currentUser = Auth::user();

        if ($pembayaran->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Pembayaran Hutang {$no_pembayaran} sudah terverifikasi.");
        }

        if ($pembayaran->id_user === ($currentUser->id_user ?? Auth::id()) && !$currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        DB::transaction(function () use ($pembayaran, $currentUser) {
            // Tentukan akun kredit (BCA 1102 atau Kas 1101)
            $isBca = str_contains($pembayaran->catatan ?? '', 'Bank BCA');
            $kreditAkun = $isBca ? 1102 : 1101;
            $namaAkun = $isBca ? 'Bank BCA' : 'Kas di Tangan';

            // Jurnal Pembayaran Hutang:
            // (D) Hutang Usaha (2101) = Total
            // (K) Kas/Bank = Total
            $entries = [
                [
                    'kode_akun' => 2101,
                    'debit' => (int) $pembayaran->total,
                    'kredit' => 0,
                    'keterangan' => "Pelunasan hutang atas nota {$pembayaran->nota_pembelian}",
                ],
                [
                    'kode_akun' => $kreditAkun,
                    'debit' => 0,
                    'kredit' => (int) $pembayaran->total,
                    'keterangan' => "Pengeluaran {$namaAkun} pembayaran hutang {$pembayaran->no_pembayaran}",
                ],
            ];

            $this->journalService->post(
                'ju_hutang',
                'no_pembayaran',
                $pembayaran->no_pembayaran,
                $pembayaran->tanggal->toDateString(),
                $entries
            );

            // Update status
            $pembayaran->status_verifikasi = 'Terverifikasi';
            $pembayaran->verified_by = $currentUser->id_user ?? Auth::id();
            $pembayaran->verified_at = now();
            $pembayaran->save();
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => "Pembayaran Hutang {$no_pembayaran} berhasil diverifikasi."]);
        }

        return redirect()->route('pembayaran-hutang.index')->with('success', "Pembayaran Hutang {$no_pembayaran} berhasil diverifikasi.");
    }
}
