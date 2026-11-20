@extends('layouts.app')

@section('title', 'Beban Operasional')
@section('subtitle', 'Catat pengeluaran operasional dan verifikasi untuk memposting jurnal beban.')

@section('content')
    @if(auth()->user()->hasAnyRole(['Owner','Kasir']))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Catat Beban Baru</summary>
            <form action="{{ route('beban.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Beban</label>
                    <input type="text" name="nama_beban" value="{{ old('nama_beban') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Akun Beban</label>
                    <select name="kode_akun" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Pilih Akun --</option>
                        @foreach($coas as $c)
                            <option value="{{ $c->kode_akun }}" @selected((string) old('kode_akun') === (string) $c->kode_akun)>
                                {{ $c->kode_akun }} — {{ $c->nama_akun }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal</label>
                    <input type="number" name="total" min="1" step="0.01" value="{{ old('total') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sumber Dana</label>
                    <select name="metode_bayar" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="Kas" @selected(old('metode_bayar') === 'Kas')>Kas</option>
                        <option value="Bank BCA" @selected(old('metode_bayar') === 'Bank BCA')>Bank BCA</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                    <input type="text" name="catatan" value="{{ old('catatan') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Beban</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. beban / nama..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="mulai" value="{{ request('mulai') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="selesai" value="{{ request('selesai') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">No. Beban</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Nama Beban</th>
                        <th class="text-left px-4 py-3">Akun</th>
                        <th class="text-right px-4 py-3">Nominal</th>
                        <th class="text-center px-4 py-3">Sumber</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($beban as $b)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $b->id_beban }}</td>
                            <td class="px-4 py-3">{{ $b->tanggal?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $b->nama_beban }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $b->coa->nama_akun ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format((float) $b->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ $b->metode_bayar ?: '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $b->status_verifikasi === 'Terverifikasi' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $b->status_verifikasi }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($b->status_verifikasi !== 'Terverifikasi' && auth()->user()->hasAnyRole(['Owner','Kasir']))
                                    <form action="{{ route('beban.verify', $b->id_beban) }}" method="POST" onsubmit="return confirm('Verifikasi beban ini? Jurnal akan diposting.')">
                                        @csrf
                                        <button class="text-xs font-semibold text-emerald-600 hover:underline">Verifikasi</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada data beban.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $beban->links() }}</div>
    </div>
@endsection
