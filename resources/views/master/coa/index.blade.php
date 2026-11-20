@extends('layouts.app')

@section('title', 'Bagan Akun (COA)')
@section('subtitle', 'Struktur akun akuntansi yang digunakan pada seluruh jurnal otomatis.')

@section('content')
    @if(auth()->user()->hasRole('Owner'))
        <details class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#172B4D]">+ Tambah Akun Baru</summary>
            <form action="{{ route('coa.store') }}" method="POST" class="border-t border-slate-100 px-5 py-5 grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Akun</label>
                    <input type="number" name="kode_akun" value="{{ old('kode_akun') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Akun</label>
                    <input type="text" name="nama_akun" value="{{ old('nama_akun') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                    <select name="kategori" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach(['Aset Lancar','Kewajiban Lancar','Ekuitas','Pendapatan','Beban Pokok','Beban Operasional'] as $k)
                            <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Saldo Normal</label>
                    <select name="saldo_normal" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="Debit" @selected(old('saldo_normal') === 'Debit')>Debit</option>
                        <option value="Kredit" @selected(old('saldo_normal') === 'Kredit')>Kredit</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button class="px-5 py-2.5 rounded-lg bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Simpan Akun</button>
                </div>
            </form>
        </details>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 p-4 border-b border-slate-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode / nama akun..."
                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="kategori" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Kategori</option>
                @foreach(['Aset Lancar','Kewajiban Lancar','Ekuitas','Pendapatan','Beban Pokok','Beban Operasional'] as $k)
                    <option value="{{ $k }}" @selected(request('kategori') === $k)>{{ $k }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-[#172B4D] text-white text-sm font-semibold">Cari</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="text-left px-4 py-3">Kode</th>
                        <th class="text-left px-4 py-3">Nama Akun</th>
                        <th class="text-left px-4 py-3">Kategori</th>
                        <th class="text-center px-4 py-3">Saldo Normal</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($coa as $c)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs">{{ $c->kode_akun }}</td>
                            <td class="px-4 py-3 font-medium">{{ $c->nama_akun }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $c->kategori }}</td>
                            <td class="px-4 py-3 text-center">{{ $c->saldo_normal }}</td>
                            <td class="px-4 py-3 text-right">
                                @if(auth()->user()->hasRole('Owner'))
                                    <form action="{{ route('coa.destroy', $c->kode_akun) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada akun COA.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">{{ $coa->links() }}</div>
    </div>
@endsection
