@extends('layouts.app')

@section('title', 'Pembayaran Hutang')
@section('subtitle', 'Catat pembayaran hutang kepada supplier atas nota pembelian kredit.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Admin Hutang','Kasir']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Catat Pembayaran Baru</summary>
            <form action="{{ route('pembayaran-hutang.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nota Kredit (terverifikasi)</label>
                    <select name="nota_pembelian" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Pilih Nota --</option>
                        @foreach($pembelians as $pb)
                            <option value="{{ $pb->nota_pembelian }}" @selected(old('nota_pembelian') === $pb->nota_pembelian)>
                                {{ $pb->nota_pembelian }} — {{ $pb->supplier->nama_supplier ?? '-' }} — Sisa: Rp {{ number_format($pb->sisa_hutang_tersedia, 0, ',', '.') }}
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dibayar Via</label>
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
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Pembayaran</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pembayaran / nota..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">No. Pembayaran</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Nota</th>
                        <th class="text-left px-4 py-3">Supplier</th>
                        <th class="text-right px-4 py-3">Nominal</th>
                        <th class="text-center px-4 py-3">Dibayar Via</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pembayaran as $p)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->no_pembayaran }}</td>
                            <td class="px-4 py-3">{{ $p->tanggal?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->nota_pembelian }}</td>
                            <td class="px-4 py-3">{{ $p->pembelian->supplier->nama_supplier ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $p->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ $p->metode_bayar ?: '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $p->status_verifikasi === 'Terverifikasi' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $p->status_verifikasi }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('pembayaran-hutang.pdf', $p->no_pembayaran) }}" target="_blank" class="text-xs font-semibold text-[#168C8C] hover:underline">PDF</a>
                                    @if($p->status_verifikasi !== 'Terverifikasi' && auth()->user()->hasAnyRole(['Owner','Admin Hutang','Kasir']))
                                        <form action="{{ route('pembayaran-hutang.verify', $p->no_pembayaran) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini? Jurnal akan diposting.')">
                                            @csrf
                                            <button class="text-xs font-semibold text-emerald-600 hover:underline">Verifikasi</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada pembayaran hutang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $pembayaran->links() }}</div>
    </div>
@endsection
