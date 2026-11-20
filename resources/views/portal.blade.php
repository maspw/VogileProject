<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Tahap 2 - PT VOGILE Distribusi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F3F5F8; }
    </style>
</head>
<body class="min-h-screen text-[#172B4D]">
    <!-- Topbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-[#172B4D] text-[#168C8C] flex items-center justify-center font-bold text-xl">
                    V
                </div>
                <div>
                    <h1 class="font-bold text-base text-[#172B4D] leading-tight">PT VOGILE DISTRIBUSI</h1>
                    <span class="text-xs text-[#168C8C] font-semibold tracking-wide uppercase">Hasil Implementasi Tahap 2</span>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-semibold text-[#172B4D]">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="text-xs text-slate-500 font-medium">Role: <span class="text-[#168C8C] font-bold">{{ auth()->user()->role->nama_role ?? 'Owner' }}</span></div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-3.5 py-2 rounded-lg border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-slate-600 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Banner -->
        <div class="bg-gradient-to-r from-[#172B4D] to-[#12385C] rounded-2xl p-6 sm:p-8 text-white shadow-xl mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-block px-3 py-1 rounded-full bg-[#168C8C]/30 text-[#40E0D0] text-xs font-bold uppercase tracking-wider mb-3">
                    Tahap 2 Selesai & Aktif
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold mb-2">Backend Services, Controller & Cetak PDF</h2>
                <p class="text-slate-300 text-sm max-w-2xl">
                    Database <strong>db_vogile</strong> aktif dengan 15 Akun COA, 10 SKU Barang, 7 Role Akuntansi, dan transaksi seimbang (Total Jurnal Rp 84.876.000).
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="/admin" class="inline-flex items-center px-5 py-3 rounded-xl bg-[#168C8C] hover:bg-[#127272] text-white font-semibold text-sm shadow-lg shadow-[#168C8C]/30 transition">
                    Buka Filament Admin Panel &rarr;
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Card 1: Dokumen Cetak PDF -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            📄
                        </div>
                        <h3 class="font-bold text-base text-[#172B4D]">Uji Cetak Dokumen PDF Resmi</h3>
                    </div>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">DomPDF Engine</span>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Klik tombol di bawah ini untuk melihat pratinjau PDF berformat standar enterprise PT VOGILE (warna Navy #172B4D & Teal #168C8C):
                </p>

                <div class="space-y-2.5">
                    <a href="/penjualan/PJ-202609-001/pdf" target="_blank"
                       class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#168C8C] hover:bg-slate-50 transition group">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-[#172B4D]">Faktur Penjualan Tunai</span>
                            <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">PJ-202609-001</span>
                        </div>
                        <span class="text-xs text-[#168C8C] font-semibold group-hover:translate-x-1 transition">Buka PDF &rarr;</span>
                    </a>

                    <a href="/penjualan/PJ-202609-002/pdf" target="_blank"
                       class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#168C8C] hover:bg-slate-50 transition group">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-[#172B4D]">Faktur Penjualan Kredit</span>
                            <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">PJ-202609-002</span>
                        </div>
                        <span class="text-xs text-[#168C8C] font-semibold group-hover:translate-x-1 transition">Buka PDF &rarr;</span>
                    </a>

                    <a href="/pembelian/PB-202609-001/pdf" target="_blank"
                       class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#168C8C] hover:bg-slate-50 transition group">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-[#172B4D]">Nota Pembelian Barang</span>
                            <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">PB-202609-001</span>
                        </div>
                        <span class="text-xs text-[#168C8C] font-semibold group-hover:translate-x-1 transition">Buka PDF &rarr;</span>
                    </a>

                    <a href="/pelunasan-piutang/PP-202609-001/pdf" target="_blank"
                       class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#168C8C] hover:bg-slate-50 transition group">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-[#172B4D]">Kuitansi Pelunasan Piutang</span>
                            <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">PP-202609-001</span>
                        </div>
                        <span class="text-xs text-[#168C8C] font-semibold group-hover:translate-x-1 transition">Buka PDF &rarr;</span>
                    </a>

                    <a href="/pembayaran-hutang/PH-202609-001/pdf" target="_blank"
                       class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#168C8C] hover:bg-slate-50 transition group">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-[#172B4D]">Bukti Pembayaran Hutang</span>
                            <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">PH-202609-001</span>
                        </div>
                        <span class="text-xs text-[#168C8C] font-semibold group-hover:translate-x-1 transition">Buka PDF &rarr;</span>
                    </a>

                    <a href="/retur-penjualan/RTJ-202609-001/pdf" target="_blank"
                       class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#168C8C] hover:bg-slate-50 transition group">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-[#172B4D]">Bukti Retur Penjualan</span>
                            <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">RTJ-202609-001</span>
                        </div>
                        <span class="text-xs text-[#168C8C] font-semibold group-hover:translate-x-1 transition">Buka PDF &rarr;</span>
                    </a>

                    <a href="/retur-pembelian/RTB-202609-001/pdf" target="_blank"
                       class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#168C8C] hover:bg-slate-50 transition group">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-semibold text-[#172B4D]">Bukti Retur Pembelian</span>
                            <span class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">RTB-202609-001</span>
                        </div>
                        <span class="text-xs text-[#168C8C] font-semibold group-hover:translate-x-1 transition">Buka PDF &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Card 2: JSON REST API Controller -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                ⚡
                            </div>
                            <h3 class="font-bold text-base text-[#172B4D]">Endpoints Controller & JSON Data</h3>
                        </div>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">REST Controllers</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">
                        Controller Tahap 2 siap mengembalikan response JSON saat dipanggil via browser / API client:
                    </p>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <a href="/barang" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">📦 Master Barang</div>
                            <div class="text-slate-400 font-mono mt-0.5">/barang</div>
                        </a>
                        <a href="/pelanggan" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">👥 Pelanggan</div>
                            <div class="text-slate-400 font-mono mt-0.5">/pelanggan</div>
                        </a>
                        <a href="/supplier" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">🏭 Supplier</div>
                            <div class="text-slate-400 font-mono mt-0.5">/supplier</div>
                        </a>
                        <a href="/coa" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">📑 Bagan Akun COA</div>
                            <div class="text-slate-400 font-mono mt-0.5">/coa</div>
                        </a>
                        <a href="/penjualan" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">💰 Transaksi Penjualan</div>
                            <div class="text-slate-400 font-mono mt-0.5">/penjualan</div>
                        </a>
                        <a href="/pembelian" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">🛒 Transaksi Pembelian</div>
                            <div class="text-slate-400 font-mono mt-0.5">/pembelian</div>
                        </a>
                        <a href="/po" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">📋 Purchase Order</div>
                            <div class="text-slate-400 font-mono mt-0.5">/po</div>
                        </a>
                        <a href="/beban" target="_blank" class="p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <div class="font-bold text-[#172B4D]">💡 Beban Operasional</div>
                            <div class="text-slate-400 font-mono mt-0.5">/beban</div>
                        </a>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-start space-x-2.5">
                        <span class="text-base">🛡️</span>
                        <div class="text-xs text-slate-600 leading-relaxed">
                            <strong>Aturan Segregation of Duties:</strong> Saat verifikasi dilakukan via controller (`POST /penjualan/{id}/verify`, `POST /pembelian/{id}/verify`), sistem memverifikasi bahwa penginput tidak boleh sama dengan pemverifikasi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
