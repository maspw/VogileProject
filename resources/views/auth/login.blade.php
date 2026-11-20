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
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#172B4D] text-[#168C8C] shadow-lg mb-4 font-bold text-2xl">
                V
            </div>
            <h1 class="text-2xl font-bold text-[#172B4D] tracking-tight">VOGILE Enterprise</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Informasi Akuntansi & Distribusi Bahan Pokok</p>
        </div>

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
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#168C8C] focus:border-transparent transition text-sm text-[#172B4D] placeholder-slate-400"
                           placeholder="nama@vogile.com atau username">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#168C8C] focus:border-transparent transition text-sm text-[#172B4D]"
                           placeholder="••••••••">
                </div>

                <label class="flex items-center text-sm text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#168C8C] focus:ring-[#168C8C] border-slate-300">
                    <span class="ml-2 text-xs">Ingat saya</span>
                </label>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-[#168C8C] hover:bg-[#127272] text-white font-semibold text-sm shadow-md shadow-[#168C8C]/20 transition duration-150">
                    Masuk ke Sistem
                </button>
            </form>

            <p class="mt-6 text-xs text-slate-400 text-center">
                Gunakan akun yang terdaftar pada seeder. Hubungi Owner bila Anda belum memiliki akses.
            </p>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; 2026 PT VOGILE Distribusi. All rights reserved.
        </p>
    </div>
</body>
</html>
