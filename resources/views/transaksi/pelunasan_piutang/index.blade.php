@extends('layouts.app')

@section('title', 'Pelunasan Piutang')
@section('subtitle', 'Catat penerimaan pembayaran dari pelanggan atas faktur kredit.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Admin Piutang','Kasir']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Catat Pelunasan Baru</summary>
            <form action="{{ route('pelunasan-piutang.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Faktur Kredit (terverifikasi)</label>
                    <select name="id_penjualan" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Pilih Faktur --</option>
                        @foreach($penjualans as $pj)
                            <option value="{{ $pj->id_penjualan }}" @selected(old('id_penjualan') === $pj->id_penjualan)>
                                {{ $pj->id_penjualan }} — {{ $pj->pelanggan->nama ?? '-' }} — Sisa: Rp {{ number_format($pj->sisa_piutang_tersedia, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal</label>
                    <input type="number" name="total" min="1" step="0.01" value="{{ old('total') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Diterima Via</label>
                    <select name="metode_bayar" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="Kas" @selected(old('metode_bayar') === 'Kas')>Kas</option>
                        <option value="Bank BCA" @selected(old('metode_bayar') === 'Bank BCA')>Bank BCA</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                    <input type="text" name="catatan" value="{{ old('catatan') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Pelunasan</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pelunasan / faktur..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">No. Pelunasan</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Faktur</th>
                        <th class="text-left px-4 py-3">Pelanggan</th>
                        <th class="text-right px-4 py-3">Nominal</th>
                        <th class="text-center px-4 py-3">Diterima Via</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pelunasans as $p)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->no_pelunasan }}</td>
                            <td class="px-4 py-3">{{ $p->tanggal?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->id_penjualan }}</td>
                            <td class="px-4 py-3">{{ $p->penjualan->pelanggan->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $p->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ $p->metode_bayar ?: '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $p->status_verifikasi === 'Terverifikasi' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $p->status_verifikasi }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('pelunasan-piutang.pdf', $p->no_pelunasan) }}" target="_blank" class="text-xs font-semibold text-[#168C8C] hover:underline">PDF</a>
                                    @if($p->status_verifikasi !== 'Terverifikasi' && auth()->user()->hasAnyRole(['Owner','Admin Piutang','Kasir']))
                                        <form action="{{ route('pelunasan-piutang.verify', $p->no_pelunasan) }}" method="POST" onsubmit="return confirm('Verifikasi pelunasan ini? Jurnal akan diposting.')">
                                            @csrf
                                            <button class="text-xs font-semibold text-emerald-600 hover:underline">Verifikasi</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada pelunasan piutang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $pelunasans->links() }}</div>
    </div>
@endsection
