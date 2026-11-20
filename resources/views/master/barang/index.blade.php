@extends('layouts.app')

@section('title', 'Master Barang')
@section('subtitle', 'Kelola data barang, harga, HPP rata-rata, dan batas stok minimum.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Admin Pembelian','Admin Penjualan']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Tambah Barang Baru</summary>
            <form action="{{ route('barang.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Barang (opsional)</label>
                    <input type="text" name="kode_barang" value="{{ old('kode_barang') }}" placeholder="Otomatis: BRGxxx"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Barang</label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Jual</label>
                    <input type="number" name="harga_barang" min="0" step="0.01" value="{{ old('harga_barang', 0) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">HPP Awal</label>
                    <input type="number" name="hpp" min="0" step="0.01" value="{{ old('hpp', 0) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan</label>
                    <input type="text" name="satuan" value="{{ old('satuan', 'Pcs') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Stok Awal</label>
                    <input type="number" name="jumlah_barang" min="0" value="{{ old('jumlah_barang', 0) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Stok Minimum</label>
                    <input type="number" name="min_stok" min="0" value="{{ old('min_stok', 10) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Barang</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode / nama barang..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="filter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Stok</option>
                <option value="menipis" @selected(request('filter') === 'menipis')>Stok Menipis</option>
                <option value="habis" @selected(request('filter') === 'habis')>Stok Habis</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">Kode</th>
                        <th class="text-left px-4 py-3">Nama Barang</th>
                        <th class="text-right px-4 py-3">Stok</th>
                        <th class="text-right px-4 py-3">Harga Jual</th>
                        <th class="text-right px-4 py-3">HPP</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($barang as $b)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $b->kode_barang }}</td>
                            <td class="px-4 py-3 font-medium">{{ $b->nama_barang }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($b->jumlah_barang, 0, ',', '.') }} {{ $b->satuan }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $b->harga_barang, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $b->hpp, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @php($badge = match($b->status_stok) { 'Habis' => 'bg-red-50 text-red-700', 'Stok Menipis' => 'bg-amber-50 text-amber-700', default => 'bg-emerald-50 text-emerald-700' })
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge }}">{{ $b->status_stok }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(auth()->user()->hasAnyRole(['Owner','Admin Pembelian','Admin Penjualan']))
                                    <form action="{{ route('barang.destroy', $b->kode_barang) }}" method="POST" onsubmit="return confirm('Hapus barang ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada data barang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $barang->links() }}</div>
    </div>
@endsection
