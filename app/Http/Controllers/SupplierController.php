<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('id_supplier', 'LIKE', "%{$s}%")
                ->orWhere('nama_supplier', 'LIKE', "%{$s}%")
                ->orWhere('no_telp', 'LIKE', "%{$s}%");
        }

        $supplier = $query->orderBy('nama_supplier')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($supplier);
        }

        return view('master.supplier.index', compact('supplier'));
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Supplier berhasil ditambahkan', 'data' => $supplier], 201);
        }

        return redirect()->route('supplier.index')->with('success', "Supplier {$supplier->nama_supplier} berhasil ditambahkan.");
    }

    public function show(string $id_supplier)
    {
        $supplier = Supplier::with(['pembelian' => function ($q) {
            $q->orderBy('tanggal', 'desc')->take(20);
        }])->findOrFail($id_supplier);

        return response()->json($supplier);
    }

    public function update(UpdateSupplierRequest $request, string $id_supplier)
    {
        $supplier = Supplier::findOrFail($id_supplier);
        $supplier->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Data supplier berhasil diperbarui', 'data' => $supplier]);
        }

        return redirect()->route('supplier.index')->with('success', "Data supplier {$supplier->nama_supplier} berhasil diperbarui.");
    }

    public function destroy(string $id_supplier)
    {
        $supplier = Supplier::findOrFail($id_supplier);

        if ($supplier->pembelian()->exists() || $supplier->purchaseOrders()->exists()) {
            return back()->with('error', "Supplier {$supplier->nama_supplier} tidak dapat dihapus karena memiliki riwayat transaksi/PO.");
        }

        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', "Supplier {$supplier->nama_supplier} berhasil dihapus.");
    }
}
