<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoaRequest;
use App\Http\Requests\UpdateCoaRequest;
use App\Models\Coa;
use Illuminate\Http\Request;

class CoaController extends Controller
{
    public function index(Request $request)
    {
        $query = Coa::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('kode_akun', 'LIKE', "%{$s}%")
                ->orWhere('nama_akun', 'LIKE', "%{$s}%");
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $coa = $query->orderBy('kode_akun')->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($coa);
        }

        return view('master.coa.index', compact('coa'));
    }

    public function store(StoreCoaRequest $request)
    {
        $coa = Coa::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Akun COA berhasil ditambahkan', 'data' => $coa], 201);
        }

        return redirect()->route('coa.index')->with('success', "Akun {$coa->kode_akun} - {$coa->nama_akun} berhasil ditambahkan.");
    }

    public function show(int $kode_akun)
    {
        $coa = Coa::findOrFail($kode_akun);
        return response()->json($coa);
    }

    public function update(UpdateCoaRequest $request, int $kode_akun)
    {
        $coa = Coa::findOrFail($kode_akun);
        $coa->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Akun COA berhasil diperbarui', 'data' => $coa]);
        }

        return redirect()->route('coa.index')->with('success', "Akun {$coa->kode_akun} - {$coa->nama_akun} berhasil diperbarui.");
    }

    public function destroy(int $kode_akun)
    {
        $coa = Coa::findOrFail($kode_akun);

        // Cek jika akun sudah terikat di jurnal umum
        if ($coa->juPenjualan()->exists() || $coa->juPembelian()->exists() || $coa->juBeban()->exists()) {
            return back()->with('error', "Akun {$coa->kode_akun} tidak dapat dihapus karena sudah memiliki riwayat jurnal transaksi.");
        }

        $coa->delete();

        return redirect()->route('coa.index')->with('success', "Akun {$coa->kode_akun} berhasil dihapus.");
    }
}
