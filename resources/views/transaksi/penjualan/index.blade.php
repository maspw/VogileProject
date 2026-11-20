@extends('layouts.app')

@section('title', 'Transaksi Penjualan')
@section('subtitle', 'Catat penjualan tunai/kredit, verifikasi untuk mengurangi stok dan memposting jurnal.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Sales','Admin Penjualan','Kasir']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Buat Penjualan Baru</summary>
            <form action="{{ route('penjualan.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pelanggan</label>
                        <select name="id_pelanggan" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($pelanggans as $pl)
                                <option value="{{ $pl->id_pelanggan }}" @selected(old('id_pelanggan') === $pl->id_pelanggan)>
                                    {{ $pl->id_pelanggan }} — {{ $pl->nama }} (Sisa limit: Rp {{ number_format($pl->sisa_limit, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Bayar</label>
                        <select name="metode_pembayaran" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="Tunai" @selected(old('metode_pembayaran') === 'Tunai')>Tunai</option>
                            <option value="Kredit" @selected(old('metode_pembayaran') === 'Kredit')>Kredit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jatuh Tempo (wajib untuk Kredit)</label>
                        <input type="date" name="jatuh_tempo" value="{{ old('jatuh_tempo') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-2">Rincian Barang</div>
                    @include('partials.item-editor', ['barangs' => $barangs])
                </div>

                <div class="flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Penjualan</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. faktur / pelanggan..."
                   class="sm:col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach(['Menunggu','Terverifikasi','Dibatalkan'] as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                @endforeach
            </select>
            <select name="metode" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Metode</option>
                <option value="Tunai" @selected(request('metode') === 'Tunai')>Tunai</option>
                <option value="Kredit" @selected(request('metode') === 'Kredit')>Kredit</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">No. Faktur</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Pelanggan</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-center px-4 py-3">Metode</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Sisa Piutang</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penjualan as $p)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->id_penjualan }}</td>
                            <td class="px-4 py-3">{{ $p->tanggal?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $p->pelanggan->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $p->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ $p->metode_pembayaran }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $p->status_verifikasi === 'Terverifikasi' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $p->status_verifikasi }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($p->sisa_piutang, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('penjualan.pdf', $p->id_penjualan) }}" target="_blank" class="text-xs font-semibold text-[#168C8C] hover:underline">PDF</a>
                                    @if($p->status_verifikasi !== 'Terverifikasi' && auth()->user()->hasAnyRole(['Owner','Sales','Admin Penjualan','Kasir']))
                                        <form action="{{ route('penjualan.verify', $p->id_penjualan) }}" method="POST" onsubmit="return confirm('Verifikasi faktur ini? Stok dan jurnal akan diposting.')">
                                            @csrf
                                            <button class="text-xs font-semibold text-emerald-600 hover:underline">Verifikasi</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada transaksi penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $penjualan->links() }}</div>
    </div>
@endsection
