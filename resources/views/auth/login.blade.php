<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT VOGILE Distribusi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F3F5F8; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#172B4D] text-[#168C8C] shadow-lg mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-[#172B4D] tracking-tight">VOGILE Enterprise</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Informasi Akuntansi & Distribusi Bahan Pokok</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 p-8">
            <h2 class="text-lg font-bold text-[#172B4D] mb-6">Masuk ke Akun Anda</h2>

            @if($errors->any())
                <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Email atau Username</label>
                    <input type="text" id="login" name="login" value="{{ old('login', 'owner@vogile.com') }}" required autofocus
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#168C8C] focus:border-transparent transition text-sm text-[#172B4D] placeholder-slate-400"
                           placeholder="nama@vogile.com atau username">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                    </div>
                    <input type="password" id="password" name="password" value="password123" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#168C8C] focus:border-transparent transition text-sm text-[#172B4D]"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#168C8C] focus:ring-[#168C8C] border-slate-300">
                        <span class="ml-2 text-xs">Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-[#168C8C] hover:bg-[#127272] text-white font-semibold text-sm shadow-md shadow-[#168C8C]/20 transition duration-150">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- Quick Account Switcher for Testing -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Akun Demo Tersedia (Password: password123)</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillAccount('owner@vogile.com')" class="text-left p-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                        <span class="font-bold block text-[#172B4D]">👑 Owner</span>
                        <span class="text-slate-500">owner@vogile.com</span>
                    </button>
                    <button type="button" onclick="fillAccount('adminpenjualan@vogile.com')" class="text-left p-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                        <span class="font-bold block text-[#172B4D]">💼 Penjualan</span>
                        <span class="text-slate-500">adminpenjualan@...</span>
                    </button>
                    <button type="button" onclick="fillAccount('adminpembelian@vogile.com')" class="text-left p-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                        <span class="font-bold block text-[#172B4D]">📦 Pembelian</span>
                        <span class="text-slate-500">adminpembelian@...</span>
                    </button>
                    <button type="button" onclick="fillAccount('kasir@vogile.com')" class="text-left p-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                        <span class="font-bold block text-[#172B4D]">💵 Kasir</span>
                        <span class="text-slate-500">kasir@vogile.com</span>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; 2026 PT VOGILE Distribusi. All rights reserved.
        </p>
    </div>

    <script>
        function fillAccount(email) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = 'password123';
        }
    </script>
</body>
</html>
