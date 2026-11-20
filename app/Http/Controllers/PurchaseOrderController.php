<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPo;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\DocumentNumberGenerator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user']);

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('no_po', 'LIKE', "%{$s}%")
                    ->orWhereHas('supplier', fn ($sub) => $sub->where('nama_supplier', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('mulai') && $request->filled('selesai')) {
            $query->periode($request->mulai, $request->selesai);
        }

        $orders = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($orders);
        }

        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $barangs = Barang::orderBy('nama_barang')->get();

        return view('transaksi.po.index', compact('orders', 'suppliers', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'id_supplier' => 'required|string|exists:supplier,id_supplier',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string|exists:barang,kode_barang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $total += $item['jumlah'] * $item['harga_satuan'];
        }

        $po = DB::transaction(function () use ($request, $total) {
            $noPo = DocumentNumberGenerator::generate('PO', 'purchase_order', 'no_po', $request->tanggal);

            $po = PurchaseOrder::create([
                'no_po' => $noPo,
                'tanggal' => $request->tanggal,
                'id_supplier' => $request->id_supplier,
                'id_user' => Auth::user()->id_user ?? Auth::id(),
                'total' => $total,
                'status' => 'Draf',
                'catatan' => $request->catatan,
            ]);

            foreach ($request->items as $item) {
                DetailPo::create([
                    'no_po' => $noPo,
                    'kode_barang' => $item['kode_barang'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                ]);
            }

            return $po;
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Purchase Order berhasil dibuat', 'data' => $po->load('details')], 201);
        }

        return redirect()->route('po.index')->with('success', "PO {$po->no_po} berhasil dibuat.");
    }

    public function show(string $no_po)
    {
        $po = PurchaseOrder::with(['supplier', 'user', 'details.barang'])->findOrFail($no_po);

        return response()->json($po);
    }

    public function updateStatus(Request $request, string $no_po)
    {
        $request->validate([
            'status' => 'required|in:Draf,Disetujui,Diproses,Selesai,Dibatalkan',
        ]);

        $po = PurchaseOrder::findOrFail($no_po);

        if (in_array($po->status, ['Selesai', 'Dibatalkan'], true)) {
            $message = "PO {$no_po} sudah {$po->status} dan tidak dapat diubah lagi.";

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $po->status = $request->status;
        $po->save();

        if ($request->wantsJson()) {
            return response()->json(['message' => "Status PO berhasil diubah menjadi {$po->status}", 'data' => $po]);
        }

        return redirect()->route('po.index')->with('success', "Status PO {$no_po} diperbarui.");
    }
}
