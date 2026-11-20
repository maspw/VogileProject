<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Vogile Enterprise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F3F5F8; }
        [x-cloak] { display: none; }
    </style>
</head>
<body class="min-h-screen text-[#172B4D]">
@php($u = auth()->user())

<header class="bg-[#172B4D] text-white sticky top-0 z-30 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-[#168C8C] text-white flex items-center justify-center font-bold text-xl">V</div>
                <div>
                    <div class="font-bold text-sm leading-tight">PT VOGILE DISTRIBUSI</div>
                    <div class="text-[11px] text-slate-300 tracking-wide uppercase">Enterprise Accounting System</div>
                </div>
            </a>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-semibold">{{ $u->nama_user ?? $u->name ?? 'User' }}</div>
                    <div class="text-[11px] text-[#40E0D0] font-semibold">{{ $u->role->nama_role ?? '-' }}</div>
                </div>
                <a href="/admin" class="hidden md:inline-flex text-xs font-semibold px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition">Admin Panel</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg border border-white/20 hover:bg-red-500 hover:border-red-500 transition">Keluar</button>
                </form>
            </div>
        </div>

        <nav class="pb-2 -mx-1 flex items-center gap-1 overflow-x-auto text-xs font-medium">
            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Dashboard</a>

            @if($u->hasAnyRole(['Owner','Admin Pembelian','Admin Penjualan']))
                <a href="{{ route('barang.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('barang.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Barang</a>
            @endif
            @if($u->hasAnyRole(['Owner','Sales','Admin Penjualan','Admin Piutang']))
                <a href="{{ route('pelanggan.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('pelanggan.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Pelanggan</a>
            @endif
            @if($u->hasAnyRole(['Owner','Admin Pembelian','Admin Hutang']))
                <a href="{{ route('supplier.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('supplier.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Supplier</a>
            @endif
            @if($u->hasRole('Owner'))
                <a href="{{ route('coa.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('coa.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">COA</a>
            @endif

            @if($u->hasAnyRole(['Owner','Admin Pembelian']))
                <a href="{{ route('po.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('po.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Purchase Order</a>
                <a href="{{ route('pembelian.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('pembelian.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Pembelian</a>
                <a href="{{ route('retur-pembelian.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('retur-pembelian.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Retur Pembelian</a>
            @endif
            @if($u->hasAnyRole(['Owner','Sales','Admin Penjualan','Kasir']))
                <a href="{{ route('penjualan.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('penjualan.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Penjualan</a>
                <a href="{{ route('retur-penjualan.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('retur-penjualan.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Retur Penjualan</a>
            @endif
            @if($u->hasAnyRole(['Owner','Admin Piutang','Kasir']))
                <a href="{{ route('pelunasan-piutang.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('pelunasan-piutang.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Pelunasan Piutang</a>
            @endif
            @if($u->hasAnyRole(['Owner','Admin Hutang','Kasir']))
                <a href="{{ route('pembayaran-hutang.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('pembayaran-hutang.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Pembayaran Hutang</a>
            @endif
            @if($u->hasAnyRole(['Owner','Kasir']))
                <a href="{{ route('beban.index') }}" class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('beban.*') ? 'bg-[#168C8C] text-white' : 'text-slate-300 hover:bg-white/10' }}">Beban</a>
            @endif
        </nav>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[#172B4D]">@yield('title', 'Dashboard')</h1>
            <p class="text-sm text-slate-500 mt-0.5">@yield('subtitle', '')</p>
        </div>
        <div class="flex items-center gap-2">@yield('actions')</div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="font-semibold mb-1">Periksa kembali input Anda:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
