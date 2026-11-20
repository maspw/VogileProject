<?php

namespace App\Http\Controllers;

use App\Models\Beban;
use App\Services\DocumentNumberGenerator;
use App\Services\JournalPostingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BebanController extends Controller
{
    protected JournalPostingService $journalService;

    public function __construct(JournalPostingService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $query = Beban::with(['user', 'verifier']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('id_beban', 'LIKE', "%{$s}%")
                ->orWhere('nama_beban', 'LIKE', "%{$s}%");
        }

        if ($request->filled('mulai') && $request->filled('selesai')) {
            $query->periode($request->mulai, $request->selesai);
        }

        $beban = $query->orderBy('tanggal', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($beban);
        }

        return view('transaksi.beban.index', compact('beban'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_beban' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'total' => 'required|numeric|min:1',
            'kode_akun' => 'required|integer|exists:coa,kode_akun',
            'metode_bayar' => 'nullable|in:Kas,Bank BCA',
            'catatan' => 'nullable|string',
        ]);

        $idBeban = DocumentNumberGenerator::generate('BBN', 'beban', 'id_beban');

        $beban = Beban::create([
            'id_beban' => $idBeban,
            'nama_beban' => $request->nama_beban,
            'tanggal' => $request->tanggal,
            'total' => $request->total,
            'id_user' => Auth::user()->id_user ?? Auth::id(),
            'status_verifikasi' => 'Menunggu',
            'catatan' => $request->catatan . " [Akun: {$request->kode_akun}]" . ($request->metode_bayar ? " [Bayar via: {$request->metode_bayar}]" : ''),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Beban operasional berhasil dicatat', 'data' => $beban], 201);
        }

        return redirect()->route('beban.index')->with('success', "Beban Operasional {$idBeban} berhasil dicatat.");
    }

    public function verify(Request $request, string $id_beban)
    {
        $beban = Beban::findOrFail($id_beban);
        $currentUser = Auth::user();

        if ($beban->status_verifikasi === 'Terverifikasi') {
            return back()->with('info', "Beban {$id_beban} sudah terverifikasi.");
        }

        if ($beban->id_user === ($currentUser->id_user ?? Auth::id()) && !$currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        DB::transaction(function () use ($beban, $currentUser) {
            // Ekstrak kode akun dari catatan jika ada, default ke 6105 (Beban Operasional)
            preg_match('/\[Akun:\s*(\d+)\]/', $beban->catatan ?? '', $matches);
            $kodeAkunBeban = !empty($matches[1]) ? (int) $matches[1] : 6105;

            // Sumber kas/bank
            $isBca = str_contains($beban->catatan ?? '', 'Bank BCA');
            $kreditAkun = $isBca ? 1102 : 1101;
            $namaAkun = $isBca ? 'Bank BCA' : 'Kas di Tangan';

            // Jurnal Beban:
            // (D) Beban Terkait (6xxx) = Total
            // (K) Kas/Bank = Total
            $entries = [
                [
                    'kode_akun' => $kodeAkunBeban,
                    'debit' => (int) $beban->total,
                    'kredit' => 0,
                    'keterangan' => $beban->nama_beban,
                ],
                [
                    'kode_akun' => $kreditAkun,
                    'debit' => 0,
                    'kredit' => (int) $beban->total,
                    'keterangan' => "Pengeluaran {$namaAkun} untuk {$beban->nama_beban}",
                ],
            ];

            $this->journalService->post(
                'ju_beban',
                'id_beban',
                $beban->id_beban,
                $beban->tanggal->toDateString(),
                $entries
            );

            // Update status
            $beban->status_verifikasi = 'Terverifikasi';
            $beban->verified_by = $currentUser->id_user ?? Auth::id();
            $beban->verified_at = now();
            $beban->save();
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => "Beban {$id_beban} berhasil diverifikasi dan jurnal diposting."]);
        }

        return redirect()->route('beban.index')->with('success', "Beban {$id_beban} berhasil diverifikasi.");
    }
}
