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
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.18); border-radius: 999px; }
    </style>
</head>
<body class="min-h-screen text-[#172B4D]">
@php
    $u = auth()->user();
    $roleName = $u?->role?->nama_role ?? 'Tanpa Role';
    $displayName = $u?->nama_user ?? $u?->name ?? 'User';
    $initials = collect(explode(' ', trim((string) $displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $initials = $initials !== '' ? $initials : 'VG';
    $menuLink = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition';
    $menuIdle = 'text-slate-300 hover:bg-white/10 hover:text-white';
    $menuActive = 'bg-[#168C8C] text-white shadow-sm';
@endphp

{{-- ============================== SIDEBAR ============================== --}}
<aside id="app-sidebar" class="fixed top-0 left-0 z-40 h-screen w-[260px] bg-[#172B4D] flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200">

    {{-- Logo card --}}
    <div class="p-4 pb-3">
        <a href="{{ route('dashboard') }}" class="block bg-white rounded-2xl px-4 py-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#168C8C] text-white flex items-center justify-center font-bold text-xl">V</div>
                <div class="leading-tight">
                    <div class="font-bold text-[15px] text-[#172B4D] tracking-tight">VOGILE</div>
                    <div class="text-[9px] font-semibold uppercase tracking-widest text-slate-500">Integrated Enterprise System</div>
                </div>
            </div>
        </a>

        {{-- Menu search --}}
        <div class="mt-3 relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
            </svg>
            <input id="menu-search" type="search" placeholder="Cari menu&hellip;"
                   class="w-full pl-9 pr-3 py-2 rounded-xl bg-white/10 border border-white/10 text-sm text-white placeholder:text-slate-400 outline-none focus:bg-white/15 focus:border-[#168C8C]">
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-4 space-y-5" id="sidebar-nav">

        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" data-menu-item
               class="{{ $menuLink }} {{ request()->routeIs('dashboard') ? $menuActive : $menuIdle }}">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10"/></svg>
                Dashboard
            </a>
        </div>

        @if($u?->hasAnyRole(['Owner', 'Sales', 'Admin Penjualan', 'Admin Piutang', 'Admin Pembelian', 'Admin Hutang']))
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Master Data</div>

                @if($u->hasAnyRole(['Owner', 'Admin Pembelian', 'Admin Penjualan']))
                    <a href="{{ route('barang.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('barang.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Barang
                    </a>
                @endif

                @if($u->hasAnyRole(['Owner', 'Sales', 'Admin Penjualan', 'Admin Piutang']))
                    <a href="{{ route('pelanggan.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('pelanggan.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm6 0a3 3 0 1 0 0-6"/></svg>
                        Pelanggan
                    </a>
                @endif

                @if($u->hasAnyRole(['Owner', 'Admin Pembelian', 'Admin Hutang']))
                    <a href="{{ route('supplier.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('supplier.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 17h8M4 7h16v10H4zM7 7V5h10v2"/></svg>
                        Supplier
                    </a>
                @endif

                @if($u->hasRole('Owner'))
                    <a href="{{ route('coa.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('coa.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg>
                        Akun / COA
                    </a>
                @endif
            </div>
        @endif

        @if($u?->hasAnyRole(['Owner', 'Admin Pembelian', 'Admin Hutang', 'Kasir']))
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Pembelian &amp; Hutang</div>

                @if($u->hasAnyRole(['Owner', 'Admin Pembelian']))
                    <a href="{{ route('po.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('po.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 4h10a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2V6a2 2 0 0 1 2-2Z"/></svg>
                        Purchase Order
                    </a>
                    <a href="{{ route('pembelian.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('pembelian.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.8h7.7a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                        Pembelian
                    </a>
                    <a href="{{ route('retur-pembelian.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('retur-pembelian.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l-4-4 4-4M5 10h11a4 4 0 0 1 0 8h-3"/></svg>
                        Retur Pembelian
                    </a>
                @endif

                @if($u->hasAnyRole(['Owner', 'Admin Hutang', 'Kasir']))
                    <a href="{{ route('pembayaran-hutang.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('pembayaran-hutang.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3m4 0h3M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                        Pembayaran Hutang
                    </a>
                @endif
            </div>
        @endif

        @if($u?->hasAnyRole(['Owner', 'Sales', 'Admin Penjualan', 'Admin Piutang', 'Kasir']))
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Penjualan &amp; Piutang</div>

                @if($u->hasAnyRole(['Owner', 'Sales', 'Admin Penjualan', 'Kasir']))
                    <a href="{{ route('penjualan.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('penjualan.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.8h7.7a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                        Penjualan
                    </a>
                    <a href="{{ route('retur-penjualan.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('retur-penjualan.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4 4-4 4M19 14H8a4 4 0 0 1 0-8h3"/></svg>
                        Retur Penjualan
                    </a>
                @endif

                @if($u->hasAnyRole(['Owner', 'Admin Piutang', 'Kasir']))
                    <a href="{{ route('pelunasan-piutang.index') }}" data-menu-item
                       class="{{ $menuLink }} {{ request()->routeIs('pelunasan-piutang.*') ? $menuActive : $menuIdle }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3m4 0h3M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                        Pelunasan Piutang
                    </a>
                @endif
            </div>
        @endif

        @if($u?->hasAnyRole(['Owner', 'Kasir']))
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Keuangan</div>
                <a href="{{ route('beban.index') }}" data-menu-item
                   class="{{ $menuLink }} {{ request()->routeIs('beban.*') ? $menuActive : $menuIdle }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-9.5c0-1.4-1.3-2.5-3-2.5s-3 1.1-3 2.5S10.3 11 12 11s3 1.1 3 2.5S13.7 16 12 16s-3-1.1-3-2.5"/></svg>
                    Beban Operasional
                </a>
            </div>
        @endif

        @if($u?->hasRole('Owner'))
            <div class="space-y-1 pt-3 mt-3 border-t border-white/10">
                <a href="/admin" data-menu-item
                   class="{{ $menuLink }} {{ request()->is('admin*') ? $menuActive : $menuIdle }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7-3a7 7 0 0 1-.1 1.2l2 1.6-2 3.4-2.4-1a7 7 0 0 1-2 1.2l-.4 2.6h-4l-.4-2.6a7 7 0 0 1-2-1.2l-2.4 1-2-3.4 2-1.6A7 7 0 0 1 5 12c0-.4 0-.8.1-1.2l-2-1.6 2-3.4 2.4 1a7 7 0 0 1 2-1.2L9.9 3h4l.4 2.6a7 7 0 0 1 2 1.2l2.4-1 2 3.4-2 1.6c.1.4.1.8.1 1.2Z"/></svg>
                    Admin Panel
                </a>
            </div>
        @endif

        <div id="menu-empty" class="hidden px-3 py-2 text-xs text-slate-400">Menu tidak ditemukan.</div>
    </nav>

    {{-- Footer profile (menyatu dengan background navy, tanpa card/border) --}}
    <div class="px-4 pb-5 pt-3 mt-auto flex items-center gap-3">
        <div class="w-10 h-10 shrink-0 rounded-lg bg-[#168C8C] text-white flex items-center justify-center text-sm font-bold tracking-wide">
            {{ $initials }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-sm font-semibold text-white truncate">{{ $displayName }}</div>
            <div class="text-[11px] text-slate-400 truncate">{{ $roleName }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="shrink-0">
            @csrf
            <button type="submit" title="Keluar"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-red-500/80 transition">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
            </button>
        </form>
    </div>
</aside>

{{-- ============================== CONTENT ============================== --}}
<div class="lg:pl-[260px] min-h-screen flex flex-col">

    {{-- Topbar --}}
    <header class="h-16 bg-white border-b border-slate-200 flex items-center gap-4 px-4 sm:px-6 lg:px-8 sticky top-0 z-30">
        <button type="button" id="sidebar-toggle" class="lg:hidden w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-[#172B4D]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="min-w-0 flex-1">
            <h1 class="text-lg font-bold text-[#172B4D] truncate">@yield('title', 'Dashboard')</h1>
            <p class="text-[11px] text-slate-500 truncate">@yield('subtitle', '')</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-right">
            <div class="text-xs text-slate-500">{{ now()->translatedFormat('d M Y') }}</div>
        </div>
        <div class="flex items-center gap-2">@yield('actions')</div>
    </header>

    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">

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

    <footer class="px-4 sm:px-6 lg:px-8 py-4 text-[11px] text-slate-400 border-t border-slate-200">
        &copy; {{ date('Y') }} PT VOGILE Distribusi &middot; Integrated Enterprise System
    </footer>
</div>

<script>
    (function () {
        const sidebar = document.getElementById('app-sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const search = document.getElementById('menu-search');
        const empty = document.getElementById('menu-empty');

        toggle?.addEventListener('click', function () {
            sidebar.classList.toggle('-translate-x-full');
        });

        search?.addEventListener('input', function () {
            const term = this.value.trim().toLowerCase();
            let visible = 0;

            document.querySelectorAll('#sidebar-nav [data-menu-item]').forEach(function (item) {
                const match = item.textContent.toLowerCase().includes(term);
                item.classList.toggle('hidden', term !== '' && !match);
                if (term === '' || match) visible++;
            });

            document.querySelectorAll('#sidebar-nav > div').forEach(function (section) {
                if (section.querySelector('[data-menu-item]')) {
                    const anyVisible = Array.from(section.querySelectorAll('[data-menu-item]'))
                        .some(function (item) { return !item.classList.contains('hidden'); });
                    section.classList.toggle('hidden', term !== '' && !anyVisible);
                }
            });

            empty?.classList.toggle('hidden', visible > 0 || term === '');
        });
    })();
</script>

@stack('scripts')
</body>
</html>
