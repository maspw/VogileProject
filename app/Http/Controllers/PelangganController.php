<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePelangganRequest;
use App\Http\Requests\UpdatePelangganRequest;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('id_pelanggan', 'LIKE', "%{$s}%")
                ->orWhere('nama', 'LIKE', "%{$s}%")
                ->orWhere('no_telp', 'LIKE', "%{$s}%");
        }

        $pelanggan = $query->orderBy('nama')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($pelanggan);
        }

        return view('master.pelanggan.index', compact('pelanggan'));
    }

    public function store(StorePelangganRequest $request)
    {
        $pelanggan = Pelanggan::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pelanggan berhasil ditambahkan', 'data' => $pelanggan], 201);
        }

        return redirect()->route('pelanggan.index')->with('success', "Pelanggan {$pelanggan->nama} berhasil ditambahkan.");
    }

    public function show(string $id_pelanggan)
    {
        $pelanggan = Pelanggan::with(['penjualan' => function ($q) {
            $q->orderBy('tanggal', 'desc')->take(20);
        }])->findOrFail($id_pelanggan);

        return response()->json($pelanggan);
    }

    public function update(UpdatePelangganRequest $request, string $id_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($id_pelanggan);
        $pelanggan->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Data pelanggan berhasil diperbarui', 'data' => $pelanggan]);
        }

        return redirect()->route('pelanggan.index')->with('success', "Data pelanggan {$pelanggan->nama} berhasil diperbarui.");
    }

    public function destroy(string $id_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($id_pelanggan);

        if ($pelanggan->penjualan()->exists()) {
            return back()->with('error', "Pelanggan {$pelanggan->nama} tidak dapat dihapus karena memiliki riwayat penjualan.");
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', "Pelanggan {$pelanggan->nama} berhasil dihapus.");
    }
}
