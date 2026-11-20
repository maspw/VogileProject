@extends('layouts.app')

@section('title', 'Master Pelanggan')
@section('subtitle', 'Data pelanggan beserta limit dan piutang berjalan.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Sales','Admin Penjualan','Admin Piutang']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Tambah Pelanggan Baru</summary>
            <form action="{{ route('pelanggan.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ID Pelanggan (opsional)</label>
                    <input type="text" name="id_pelanggan" value="{{ old('id_pelanggan') }}" placeholder="Otomatis: PLGxxx"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Pelanggan</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">No. Telepon</label>
                    <input type="text" name="no_telp" value="{{ old('no_telp') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Limit Piutang</label>
                    <input type="number" name="limit_piutang" min="0" step="0.01" value="{{ old('limit_piutang', 0) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat</label>
                    <input type="text" name="alamat" value="{{ old('alamat') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Pelanggan</button>
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
                        <th class="text-left px-4 py-3">Nama</th>
                        <th class="text-left px-4 py-3">Telepon</th>
                        <th class="text-right px-4 py-3">Limit Piutang</th>
                        <th class="text-right px-4 py-3">Piutang Berjalan</th>
                        <th class="text-right px-4 py-3">Sisa Limit</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pelanggan as $p)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->id_pelanggan }}</td>
                            <td class="px-4 py-3 font-medium">{{ $p->nama }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->no_telp ?: '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $p->limit_piutang, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($p->total_piutang, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $p->sisa_limit <= 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                Rp {{ number_format($p->sisa_limit, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(auth()->user()->hasAnyRole(['Owner','Sales','Admin Penjualan','Admin Piutang']))
                                    <form action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}" method="POST" onsubmit="return confirm('Hapus pelanggan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada data pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $pelanggan->links() }}</div>
    </div>
@endsection
