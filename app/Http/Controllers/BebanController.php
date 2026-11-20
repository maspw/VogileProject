<?php

namespace App\Http\Controllers;

use App\Models\Beban;
use App\Models\Coa;
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
        $query = Beban::with(['user', 'verifier', 'coa']);

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('id_beban', 'LIKE', "%{$s}%")
                    ->orWhere('nama_beban', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('mulai') && $request->filled('selesai')) {
            $query->periode($request->mulai, $request->selesai);
        }

        $beban = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($beban);
        }

        $coas = Coa::where('kategori', 'Beban Operasional')->orderBy('kode_akun')->get();

        return view('transaksi.beban.index', compact('beban', 'coas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_beban' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'total' => 'required|numeric|min:1',
            'kode_akun' => 'required|integer|exists:coa,kode_akun',
            'metode_bayar' => 'required|in:Kas,Bank BCA',
            'catatan' => 'nullable|string',
        ]);

        $idBeban = DocumentNumberGenerator::generate('BBN', 'beban', 'id_beban', $request->tanggal);

        $beban = Beban::create([
            'id_beban' => $idBeban,
            'nama_beban' => $request->nama_beban,
            'tanggal' => $request->tanggal,
            'total' => $request->total,
            'kode_akun' => $request->kode_akun,
            'metode_bayar' => $request->metode_bayar,
            'id_user' => Auth::user()->id_user ?? Auth::id(),
            'status_verifikasi' => 'Menunggu',
            'catatan' => $request->catatan,
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

        if ($beban->id_user === ($currentUser->id_user ?? Auth::id()) && ! $currentUser->hasRole('Owner') && $currentUser->id_user !== 'U000') {
            abort(403, 'Pelanggaran Segregation of Duties: Anda tidak berhak memverifikasi transaksi yang Anda buat sendiri.');
        }

        try {
            DB::transaction(function () use ($beban, $currentUser) {
                $kodeAkunBeban = (int) ($beban->kode_akun ?? 6105);
                $kreditAkun = $beban->metode_bayar === 'Bank BCA' ? 1102 : 1101;
                $namaAkun = $beban->metode_bayar === 'Bank BCA' ? 'Bank BCA' : 'Kas di Tangan';
                $total = (int) round((float) $beban->total);

                $entries = [
                    [
                        'kode_akun' => $kodeAkunBeban,
                        'debit' => $total,
                        'kredit' => 0,
                        'keterangan' => $beban->nama_beban,
                    ],
                    [
                        'kode_akun' => $kreditAkun,
                        'debit' => 0,
                        'kredit' => $total,
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

                $beban->status_verifikasi = 'Terverifikasi';
                $beban->verified_by = $currentUser->id_user ?? Auth::id();
                $beban->verified_at = now();
                $beban->save();
            });
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => "Beban {$id_beban} berhasil diverifikasi dan jurnal diposting."]);
        }

        return redirect()->route('beban.index')->with('success', "Beban {$id_beban} berhasil diverifikasi.");
    }
}
