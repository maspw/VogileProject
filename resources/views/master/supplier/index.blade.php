@extends('layouts.app')

@section('title', 'Master Supplier')
@section('subtitle', 'Data supplier beserta hutang berjalan.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Admin Pembelian','Admin Hutang']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Tambah Supplier Baru</summary>
            <form action="{{ route('supplier.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ID Supplier (opsional)</label>
                    <input type="text" name="id_supplier" value="{{ old('id_supplier') }}" placeholder="Otomatis: SUPxxx"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Supplier</label>
                    <input type="text" name="nama_supplier" value="{{ old('nama_supplier') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">No. Telepon</label>
                    <input type="text" name="no_telp" value="{{ old('no_telp') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat</label>
                    <input type="text" name="alamat" value="{{ old('alamat') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Supplier</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID / nama / telepon..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Nama Supplier</th>
                        <th class="text-left px-4 py-3">Telepon</th>
                        <th class="text-left px-4 py-3">Email</th>
                        <th class="text-right px-4 py-3">Hutang Berjalan</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplier as $s)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $s->id_supplier }}</td>
                            <td class="px-4 py-3 font-medium">{{ $s->nama_supplier }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s->no_telp ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s->email ?: '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($s->total_hutang, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                @if(auth()->user()->hasAnyRole(['Owner','Admin Pembelian','Admin Hutang']))
                                    <form action="{{ route('supplier.destroy', $s->id_supplier) }}" method="POST" onsubmit="return confirm('Hapus supplier ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada data supplier.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $supplier->links() }}</div>
    </div>
@endsection
