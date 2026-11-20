@extends('layouts.app')

@php
    $u = auth()->user();
    $role = $u?->role?->nama_role ?? 'Tanpa Role';
    $startOfMonth = now()->startOfMonth()->toDateString();
    $endOfMonth = now()->endOfMonth()->toDateString();

    $rp = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

@section('title', 'Dashboard ' . $role)
@section('subtitle', 'Ringkasan operasional sesuai hak akses ' . $role . '.')

@section('content')
    @php
        $cards = [];

        $card = function (string $label, string $value, string $caption, string $tone = 'slate') use (&$cards) {
            $cards[] = compact('label', 'value', 'caption', 'tone');
        };

        if ($role === 'Owner') {
            $card('Barang Terdaftar', number_format(\App\Models\Barang::count(), 0, ',', '.') . ' SKU', \App\Models\Barang::stokMenipis()->count() . ' menipis · ' . \App\Models\Barang::stokHabis()->count() . ' habis', 'amber');
            $card('Penjualan Bulan Ini', $rp(\App\Models\Penjualan::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Hanya transaksi terverifikasi');
            $card('Pembelian Bulan Ini', $rp(\App\Models\Pembelian::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Hanya transaksi terverifikasi');
            $card('Menunggu Verifikasi', (\App\Models\Penjualan::menunggu()->count() + \App\Models\Pembelian::menunggu()->count()) . ' dokumen', 'Penjualan + pembelian', 'rose');
        } elseif ($role === 'Kasir') {
            $card('Penjualan Hari Ini', $rp(\App\Models\Penjualan::terverifikasi()->periode(now()->toDateString(), now()->toDateString())->sum('total')), 'Transaksi tunai & kredit', 'emerald');
            $card('Pelunasan Bulan Ini', $rp(\App\Models\PelunasanPiutang::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Penerimaan piutang', 'emerald');
            $card('Beban Belum Verifikasi', \App\Models\Beban::menunggu()->count() . ' dokumen', 'Perlu ditinjau Owner', 'amber');
            $card('Pembayaran Hutang', \App\Models\PembayaranHutang::menunggu()->count() . ' menunggu', 'Menunggu verifikasi', 'rose');
        } elseif ($role === 'Admin Pembelian') {
            $card('Purchase Order', \App\Models\PurchaseOrder::count() . ' dokumen', \App\Models\PurchaseOrder::whereIn('status', ['Draf', 'Disetujui', 'Diproses'])->count() . ' dalam proses', 'amber');
            $card('Pembelian Bulan Ini', $rp(\App\Models\Pembelian::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Transaksi terverifikasi');
            $card('Stok Menipis', \App\Models\Barang::stokMenipis()->count() . ' SKU', \App\Models\Barang::stokHabis()->count() . ' SKU habis', 'rose');
            $card('Hutang Usaha', $rp(\App\Models\Supplier::all()->sum(fn ($s) => $s->total_hutang)), 'Saldo berjalan supplier');
        } elseif ($role === 'Admin Hutang') {
            $card('Hutang Usaha', $rp(\App\Models\Supplier::all()->sum(fn ($s) => $s->total_hutang)), 'Saldo berjalan supplier', 'amber');
            $card('Pembayaran Bulan Ini', $rp(\App\Models\PembayaranHutang::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Pembayaran terverifikasi', 'emerald');
            $card('Pembelian Kredit', $rp(\App\Models\Pembelian::terverifikasi()->kredit()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Pembelian belum lunas');
            $card('Menunggu Verifikasi', \App\Models\PembayaranHutang::menunggu()->count() . ' pembayaran', 'Perlu ditinjau Owner', 'rose');
        } elseif ($role === 'Admin Penjualan') {
            $card('Penjualan Bulan Ini', $rp(\App\Models\Penjualan::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Transaksi terverifikasi', 'emerald');
            $card('Menunggu Verifikasi', \App\Models\Penjualan::menunggu()->count() . ' faktur', 'Penjualan belum diverifikasi', 'amber');
            $card('Retur Bulan Ini', \App\Models\ReturPenjualan::terverifikasi()->periode($startOfMonth, $endOfMonth)->count() . ' dokumen', 'Retur penjualan', 'rose');
            $card('Piutang Usaha', $rp(\App\Models\Pelanggan::all()->sum(fn ($p) => $p->total_piutang)), 'Saldo berjalan pelanggan');
        } elseif ($role === 'Admin Piutang') {
            $card('Piutang Usaha', $rp(\App\Models\Pelanggan::all()->sum(fn ($p) => $p->total_piutang)), 'Saldo berjalan pelanggan', 'amber');
            $card('Pelunasan Bulan Ini', $rp(\App\Models\PelunasanPiutang::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('jumlah_bayar')), 'Penerimaan terverifikasi', 'emerald');
            $card('Menunggu Verifikasi', \App\Models\PelunasanPiutang::menunggu()->count() . ' bukti', 'Pelunasan belum diverifikasi', 'rose');
            $card('Pelanggan Aktif', number_format(\App\Models\Pelanggan::count(), 0, ',', '.') . ' pelanggan', 'Terdaftar di master data');
        } elseif ($role === 'Sales') {
            $card('Penjualan Bulan Ini', $rp(\App\Models\Penjualan::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Transaksi terverifikasi', 'emerald');
            $card('Penjualan Kredit', $rp(\App\Models\Penjualan::terverifikasi()->kredit()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Piutang pelanggan');
            $card('Pelanggan', number_format(\App\Models\Pelanggan::count(), 0, ',', '.') . ' pelanggan', 'Basis pelanggan aktif');
            $card('Retur Penjualan', \App\Models\ReturPenjualan::periode($startOfMonth, $endOfMonth)->count() . ' dokumen', 'Bulan berjalan', 'rose');
        } else {
            $card('Penjualan Bulan Ini', $rp(\App\Models\Penjualan::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Transaksi terverifikasi');
            $card('Pembelian Bulan Ini', $rp(\App\Models\Pembelian::terverifikasi()->periode($startOfMonth, $endOfMonth)->sum('total')), 'Transaksi terverifikasi');
            $card('Piutang Usaha', $rp(\App\Models\Pelanggan::all()->sum(fn ($p) => $p->total_piutang)), 'Saldo berjalan pelanggan');
            $card('Hutang Usaha', $rp(\App\Models\Supplier::all()->sum(fn ($s) => $s->total_hutang)), 'Saldo berjalan supplier');
        }

        $toneClass = [
            'slate' => 'text-slate-500',
            'emerald' => 'text-emerald-600',
            'amber' => 'text-amber-600',
            'rose' => 'text-rose-600',
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        @foreach($cards as $c)
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $c['label'] }}</div>
                <div class="text-2xl font-bold mt-1 text-[#172B4D]">{{ $c['value'] }}</div>
                <div class="text-xs mt-1 {{ $toneClass[$c['tone']] ?? $toneClass['slate'] }}">{{ $c['caption'] }}</div>
            </div>
        @endforeach
    </div>

    @if($role === 'Owner')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
                <h2 class="font-bold text-[#172B4D] mb-1">Posisi Piutang &amp; Hutang</h2>
                <p class="text-xs text-slate-500 mb-5">Saldo berjalan berdasarkan transaksi terverifikasi dan retur.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4">
                        <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Piutang Usaha</div>
                        <div class="text-xl font-bold text-emerald-800 mt-1">{{ $rp(\App\Models\Pelanggan::all()->sum(fn ($p) => $p->total_piutang)) }}</div>
                    </div>
                    <div class="rounded-xl bg-amber-50 border border-amber-100 p-4">
                        <div class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Hutang Usaha</div>
                        <div class="text-xl font-bold text-amber-800 mt-1">{{ $rp(\App\Models\Supplier::all()->sum(fn ($s) => $s->total_hutang)) }}</div>
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
    @else
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="font-bold text-[#172B4D] mb-1">Akses Cepat</h2>
            <p class="text-xs text-slate-500 mb-4">Menu yang tersedia untuk role {{ $role }}.</p>
            <div class="flex flex-wrap gap-2">
                @if($u->hasAnyRole(['Owner', 'Sales', 'Admin Penjualan', 'Kasir']))
                    <a href="{{ route('penjualan.index') }}" class="px-4 py-2 rounded-xl bg-[#168C8C] text-white text-sm font-semibold hover:bg-[#127272] transition">Penjualan</a>
                    <a href="{{ route('retur-penjualan.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-[#172B4D] hover:border-[#168C8C] hover:text-[#168C8C] transition">Retur Penjualan</a>
                @endif
                @if($u->hasAnyRole(['Owner', 'Admin Piutang', 'Kasir']))
                    <a href="{{ route('pelunasan-piutang.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-[#172B4D] hover:border-[#168C8C] hover:text-[#168C8C] transition">Pelunasan Piutang</a>
                @endif
                @if($u->hasAnyRole(['Owner', 'Admin Pembelian']))
                    <a href="{{ route('po.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-[#172B4D] hover:border-[#168C8C] hover:text-[#168C8C] transition">Purchase Order</a>
                    <a href="{{ route('pembelian.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-[#172B4D] hover:border-[#168C8C] hover:text-[#168C8C] transition">Pembelian</a>
                @endif
                @if($u->hasAnyRole(['Owner', 'Admin Hutang', 'Kasir']))
                    <a href="{{ route('pembayaran-hutang.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-[#172B4D] hover:border-[#168C8C] hover:text-[#168C8C] transition">Pembayaran Hutang</a>
                @endif
                @if($u->hasAnyRole(['Owner', 'Kasir']))
                    <a href="{{ route('beban.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-[#172B4D] hover:border-[#168C8C] hover:text-[#168C8C] transition">Beban Operasional</a>
                @endif
            </div>
        </div>
    @endif
@endsection
