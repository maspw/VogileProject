@extends('layouts.app')

@section('title', 'Retur Penjualan')
@section('subtitle', 'Catat barang yang dikembalikan pelanggan dan verifikasi untuk mengembalikan stok serta jurnal.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Sales','Admin Penjualan','Kasir']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Buat Retur Penjualan Baru</summary>
            <form action="{{ route('retur-penjualan.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Faktur Penjualan (terverifikasi)</label>
                        <select name="id_penjualan" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- Pilih Faktur --</option>
                            @foreach($penjualans as $pj)
                                <option value="{{ $pj->id_penjualan }}" @selected(old('id_penjualan') === $pj->id_penjualan)>
                                    {{ $pj->id_penjualan }} — {{ $pj->pelanggan->nama ?? '-' }} — Rp {{ number_format((float) $pj->total, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan Retur</label>
                        <input type="text" name="alasan" value="{{ old('alasan') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-2">Barang yang Dikembalikan</div>
                    @include('partials.item-editor', ['barangs' => $barangs])
                </div>

                <div class="flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Retur</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. retur / faktur..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">No. Retur</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Faktur</th>
                        <th class="text-left px-4 py-3">Pelanggan</th>
                        <th class="text-left px-4 py-3">Alasan</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returs as $r)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $r->no_retur_penjualan }}</td>
                            <td class="px-4 py-3">{{ $r->tanggal?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $r->id_penjualan }}</td>
                            <td class="px-4 py-3">{{ $r->penjualan->pelanggan->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->alasan }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $r->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $r->status_verifikasi === 'Terverifikasi' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $r->status_verifikasi }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('retur-penjualan.pdf', $r->no_retur_penjualan) }}" target="_blank" class="text-xs font-semibold text-[#168C8C] hover:underline">PDF</a>
                                    @if($r->status_verifikasi !== 'Terverifikasi' && auth()->user()->hasAnyRole(['Owner','Sales','Admin Penjualan','Kasir']))
                                        <form action="{{ route('retur-penjualan.verify', $r->no_retur_penjualan) }}" method="POST" onsubmit="return confirm('Verifikasi retur ini? Stok dan jurnal akan diposting.')">
                                            @csrf
                                            <button class="text-xs font-semibold text-emerald-600 hover:underline">Verifikasi</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada retur penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $returs->links() }}</div>
    </div>
@endsection
