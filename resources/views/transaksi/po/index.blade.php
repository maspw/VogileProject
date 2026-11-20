@extends('layouts.app')

@section('title', 'Purchase Order')
@section('subtitle', 'Buat pesanan pembelian ke supplier dan pantau statusnya.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Admin Pembelian']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Buat Purchase Order Baru</summary>
            <form action="{{ route('po.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Supplier</label>
                        <select name="id_supplier" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id_supplier }}" @selected(old('id_supplier') === $s->id_supplier)>
                                    {{ $s->id_supplier }} — {{ $s->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                        <input type="text" name="catatan" value="{{ old('catatan') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-2">Rincian Barang</div>
                    @include('partials.item-editor', ['barangs' => $barangs])
                </div>

                <div class="flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan PO</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. PO / supplier..."
                   class="sm:col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach(['Draf','Disetujui','Diproses','Selesai','Dibatalkan'] as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">No. PO</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Supplier</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Ubah Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $po)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $po->no_po }}</td>
                            <td class="px-4 py-3">{{ $po->tanggal?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $po->supplier->nama_supplier ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $po->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php($color = match($po->status) { 'Selesai' => 'bg-emerald-50 text-emerald-700', 'Dibatalkan' => 'bg-red-50 text-red-700', 'Draf' => 'bg-slate-100 text-slate-600', default => 'bg-blue-50 text-blue-700' })
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $color }}">{{ $po->status }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if(auth()->user()->hasAnyRole(['Owner','Admin Pembelian']) && !in_array($po->status, ['Selesai','Dibatalkan']))
                                    <form action="{{ route('po.updateStatus', $po->no_po) }}" method="POST" class="flex items-center justify-end gap-2">
                                        @csrf @method('PATCH')
                                        <select name="status" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                                            @foreach(['Draf','Disetujui','Diproses','Selesai','Dibatalkan'] as $st)
                                                <option value="{{ $st }}" @selected($po->status === $st)>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                        <button class="text-xs font-semibold text-[#168C8C] hover:underline">Simpan</button>
                                    </form>
                                @else
                                    <div class="text-right text-xs text-slate-400">-</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada purchase order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $orders->links() }}</div>
    </div>
@endsection
