@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan operasional PT VOGILE Distribusi.')

@section('content')
    @php
        $totalBarang = \App\Models\Barang::count();
        $stokMenipis = \App\Models\Barang::stokMenipis()->count();
        $stokHabis = \App\Models\Barang::stokHabis()->count();
        $penjualanBulanIni = \App\Models\Penjualan::terverifikasi()->periode(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString())->sum('total');
        $pembelianBulanIni = \App\Models\Pembelian::terverifikasi()->periode(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString())->sum('total');
        $piutang = \App\Models\Pelanggan::all()->sum(fn ($p) => $p->total_piutang);
        $hutang = \App\Models\Supplier::all()->sum(fn ($s) => $s->total_hutang);
        $menungguVerifikasi = \App\Models\Penjualan::menunggu()->count() + \App\Models\Pembelian::menunggu()->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Barang Terdaftar</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($totalBarang, 0, ',', '.') }} SKU</div>
            <div class="text-xs mt-1 text-amber-600">{{ $stokMenipis }} menipis · {{ $stokHabis }} habis</div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Penjualan Bulan Ini</div>
            <div class="text-2xl font-bold mt-1">Rp {{ number_format((float) $penjualanBulanIni, 0, ',', '.') }}</div>
            <div class="text-xs mt-1 text-slate-500">Hanya transaksi terverifikasi</div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pembelian Bulan Ini</div>
            <div class="text-2xl font-bold mt-1">Rp {{ number_format((float) $pembelianBulanIni, 0, ',', '.') }}</div>
            <div class="text-xs mt-1 text-slate-500">Hanya transaksi terverifikasi</div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Menunggu Verifikasi</div>
            <div class="text-2xl font-bold mt-1">{{ $menungguVerifikasi }} dokumen</div>
            <div class="text-xs mt-1 text-slate-500">Penjualan + pembelian</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="font-bold text-[#172B4D] mb-1">Posisi Piutang &amp; Hutang</h2>
            <p class="text-xs text-slate-500 mb-5">Saldo berjalan berdasarkan transaksi terverifikasi dan retur.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4">
                    <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Piutang Usaha</div>
                    <div class="text-xl font-bold text-emerald-800 mt-1">Rp {{ number_format((float) $piutang, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-xl bg-amber-50 border border-amber-100 p-4">
                    <div class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Hutang Usaha</div>
                    <div class="text-xl font-bold text-amber-800 mt-1">Rp {{ number_format((float) $hutang, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-[#172B4D] text-white shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h2 class="font-bold mb-1">Admin Panel</h2>
                <p class="text-xs text-slate-300 leading-relaxed">Kelola master data, pantau seluruh transaksi, dan tinjau fraud alert melalui Filament Admin Panel.</p>
            </div>
            <a href="/admin" class="mt-5 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#168C8C] hover:bg-[#127272] text-sm font-semibold transition">
                Buka Admin Panel &rarr;
            </a>
        </div>
    </div>
@endsection
