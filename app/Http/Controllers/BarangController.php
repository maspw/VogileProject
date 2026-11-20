<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('kode_barang', 'LIKE', "%{$s}%")
                ->orWhere('nama_barang', 'LIKE', "%{$s}%");
        }

        if ($request->filter === 'menipis') {
            $query->stokMenipis();
        } elseif ($request->filter === 'habis') {
            $query->stokHabis();
        }

        $barang = $query->orderBy('nama_barang')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($barang);
        }

        return view('master.barang.index', compact('barang'));
    }

    public function store(StoreBarangRequest $request)
    {
        $data = $request->validated();
        $initialQty = (int) ($data['jumlah_barang'] ?? 0);
        $data['jumlah_barang'] = 0; // Akan diisi melalui StockService kartu_stok jika initialQty > 0

        $barang = Barang::create($data);

        if ($initialQty > 0) {
            $this->stockService->recordMasuk(
                $barang->kode_barang,
                $initialQty,
                (float) $barang->hpp,
                'Saldo Awal',
                'OPN-' . date('Ymd'),
                'Saldo Awal Pendaftaran Barang Baru'
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Barang berhasil ditambahkan', 'data' => $barang], 201);
        }

        return redirect()->route('barang.index')->with('success', "Barang {$barang->nama_barang} berhasil ditambahkan.");
    }

    public function show(string $kode_barang)
    {
        $barang = Barang::with(['kartuStok' => function ($q) {
            $q->orderBy('tanggal', 'desc')->take(50);
        }])->findOrFail($kode_barang);

        return response()->json($barang);
    }

    public function update(UpdateBarangRequest $request, string $kode_barang)
    {
        $barang = Barang::findOrFail($kode_barang);
        $barang->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Data barang berhasil diperbarui', 'data' => $barang]);
        }

        return redirect()->route('barang.index')->with('success', "Data barang {$barang->nama_barang} berhasil diperbarui.");
    }

    public function destroy(string $kode_barang)
    {
        $barang = Barang::findOrFail($kode_barang);

        // Cek jika barang sudah memiliki riwayat mutasi/transaksi
        if ($barang->kartuStok()->count() > 1 || $barang->detailPenjualan()->exists() || $barang->detailPembelian()->exists()) {
            return back()->with('error', "Barang {$barang->nama_barang} tidak dapat dihapus karena sudah memiliki riwayat transaksi/stok.");
        }

        $barang->delete();

        return redirect()->route('barang.index')->with('success', "Barang {$barang->nama_barang} berhasil dihapus.");
    }
}
