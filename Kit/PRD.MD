<role>
Bertindaklah sebagai Principal Full-Stack Laravel Architect, Senior UI/UX Engineer (spesialis Enterprise Dashboard Design System), Senior Accounting Information System Engineer, dan AI Integration Engineer (OCR Document AI & Fraud Detection). Tugasmu adalah mengubah dokumen PRD terlampir menjadi kode produksi (production-ready code) untuk aplikasi "VOGILE" (Sistem Informasi Akuntansi Enterprise Distribusi Barang Dagang dengan 7 aktor — Owner, Sales, Admin Penjualan, Admin Piutang, Admin Pembelian, Admin Hutang, Kasir — yang dilengkapi OCR AI pembaca nota dan Fraud AI pendeteksi kecurangan data).
</role>

<tech_stack_constraints>

1. Backend: PHP 8.2.12 & Laravel 12.x (berjalan lokal via XAMPP/Laragon). Seluruh kode WAJIB kompatibel dengan PHP 8.2: DILARANG memakai fitur PHP 8.3+ seperti typed class constants, atribut `#[\Override]`, `json_validate()`, property hooks, atau asymmetric visibility. Versi package harus mendukung Laravel 12 & PHP 8.2 (`livewire/livewire:^3`, `maatwebsite/excel:^3.1`, `barryvdh/laravel-dompdf:^3`).
2. Database: MariaDB 10.4.32 (XAMPP)/MySQL 8.0 (InnoDB, utf8mb4) dengan skema `vogos` yang SUDAH ADA. Nama tabel, kolom, primary key (termasuk composite PK pada tabel `detail_*` dan `ju_*`), dan foreign key eksisting DILARANG diubah. Penyesuaian hanya lewat migration ALTER (perbaikan tipe data) & tabel/kolom tambahan sesuai Bagian 4 PRD (`supplier`, `purchase_order`, `detail_po`, kolom verifikasi, `kartu_stok`, `ocr_scans`, `fraud_alerts`, `activity_logs`, VIEW `v_jurnal_umum`), dengan kolom JSON untuk hasil OCR dan bukti fraud.
3. Frontend & UI: Laravel Blade + Livewire 3 + Alpine.js + Tailwind CSS (Vite), ikon Heroicons/Lucide, grafik Chart.js.
4. OCR AI Engine: driver pluggable via `.env` (`OCR_DRIVER=gemini|openai|tesseract`) memakai Laravel HTTP Client dan diproses di Queue Job. Output wajib berupa JSON terstruktur (nomor nota, tanggal, pihak, items, total). Item dicocokkan ke master `barang`/`supplier`/`pelanggan` dengan fuzzy matching, lalu ditampilkan di layar review split-view sebelum disimpan sebagai draf transaksi.
5. Fraud AI Engine: `FraudDetectionService` hibrid (rule-based F-01 s.d. F-12 + statistik z-score/IQR rolling 90 hari + opsional LLM untuk narasi penjelasan) yang dijalankan Laravel Scheduler (`fraud:scan` setiap jam) dan event `TransactionVerified`, dengan skor risiko 0–100 (Rendah/Sedang/Tinggi/Kritis).
6. Kepatuhan UI/UX Enterprise: WAJIB mematuhi seluruh aturan di Bagian 9 PRD dan desain referensi Stock Card (Palet Navy #172B4D, Teal #168C8C, Canvas #F3F5F8; sidebar navy fixed 256px dengan logo VOGILE di kartu putih, pencarian menu, label section kapital, dan menu aktif ber-bg teal; topbar 80px dengan breadcrumb & global search "Cari SKU, faktur, pelanggan…"; card putih rounded dengan border tipis; header tabel huruf kapital; Rupiah rata kanan; Masuk hijau "+n" & Keluar merah "−n"; font Inter; format "Rp 1.250.000" & "14 Nov 2026").
   </tech_stack_constraints>

<strict_coding_rules>

- DILARANG menyingkat kode dengan komentar seperti `// ... tambahkan kode lainnya di sini` atau `<!-- ... -->`. Tulis seluruh kode secara lengkap, utuh, dan siap dijalankan.
- Gunakan praktik terbaik Laravel 12 (Form Request Validation, Eloquent Relationships sesuai FK skema `vogos`, Service Layer `JournalPostingService`/`StockService`/`DocumentNumberGenerator`, Database Transactions + `lockForUpdate`, Queue Jobs untuk OCR AI, Policy & middleware `role:` berbasis tabel `role` eksisting). Model tabel eksisting wajib memakai `$table`, `$primaryKey` string, `$incrementing = false`, `$timestamps = false`, dan tabel ber-composite PK ditulis lewat Query Builder/`upsert`.
- Pastikan posting jurnal otomatis (perpetual, moving average) hanya terjadi saat status berubah menjadi `Terverifikasi`, selalu SEIMBANG (Σdebit = Σkredit), idempotent (cek jurnal sudah ada sebelum insert, akun yang sama di-group karena composite PK `ju_*`), dan berjalan dalam satu `DB::transaction` bersama update stok & `kartu_stok`. Aturan segregation of duties (penginput ≠ pemverifikasi) WAJIB divalidasi di sisi server, bukan hanya disembunyikan di UI.
  </strict_coding_rules>

<execution_plan>
Karena sistem ini kompleks, kita akan mengerjakannya dalam 4 Tahap (Phase) secara berurutan agar setiap file ditulis 100% lengkap tanpa terpotong batas token.

Untuk balasan pertamamu sekarang, kerjakan **TAHAP 1 (Database, Models & Seeders)** secara lengkap:

1. Struktur folder proyek Laravel 12 & daftar perintah instalasi package (`livewire/livewire`, `maatwebsite/excel`, `barryvdh/laravel-dompdf`) beserta konfigurasi `.env` (DB `vogos`, `QUEUE_CONNECTION=database`, `OCR_DRIVER`, API key AI).
2. File Migration MySQL lengkap untuk seluruh tabel eksisting (`role`, `users`, `coa`, `barang`, `pelanggan`, `penjualan`, `detail_penjualan`, `retur_penjualan`, `detail_retur_penjualan`, `pelunasan_piutang`, `pembelian`, `detail_pembelian`, `retur_pembelian`, `detail_retur_pembelian`, `pembayaran_hutang`, `beban`, `ju_penjualan`, `ju_retur_penj`, `ju_piutang`, `ju_pembelian`, `ju_retur_pemb`, `ju_hutang`, `ju_beban`), migration ALTER perbaikan tipe data (`pass_user` VARCHAR(255), uang ke DECIMAL, debit/kredit ke BIGINT, `id_beban` VARCHAR(20)), serta tabel tambahan (`supplier`, `purchase_order`, `detail_po`, `kartu_stok`, `ocr_scans`, `fraud_alerts`, `activity_logs`) dan VIEW `v_jurnal_umum`.
3. Seluruh file Eloquent Model lengkap dengan `$fillable`, `$casts` (termasuk cast `array`/`json` untuk `hasil_json`, `bukti_json`, `data_lama`, `data_baru`), relasi sesuai FK, accessor (sisa piutang, sisa hutang, status stok Aman/Stok Menipis/Habis), dan helper scopes (`scopeTerverifikasi`, `scopeMenunggu`, `scopePeriode`, `scopeJatuhTempo`).
4. File `DatabaseSeeder.php` berisi data realistis PT VOGILE Distribusi (7 role & 1 user per role, Bagan Akun/COA, 10 barang beserta saldo awal OPN di `kartu_stok`, 5 supplier, 8 pelanggan, contoh PO → pembelian tunai & kredit, penjualan tunai & kredit, retur penjualan & retur pembelian, pelunasan piutang sebagian, pembayaran hutang, beban operasional 3 bulan dengan jurnal `ju_*` yang seimbang, termasuk kasus data anomali — nota duplikat, harga jual di bawah HPP, penginput = pemverifikasi, dan beban melonjak — untuk menguji Fraud AI).

Setelah selesai menulis Tahap 1, berhenti dan tanyakan apakah saya siap lanjut ke:

- **Tahap 2:** Backend Controllers, Autentikasi & Middleware Role, CRUD Master Data (Akun, Barang, Pelanggan, Supplier), Transaksi (Penjualan, Pembelian, PO, Retur, Pembayaran Hutang, Pembayaran Piutang, Beban) beserta alur Verifikasi, `JournalPostingService`, `StockService`, & Cetak PDF (faktur, bukti retur, bukti pembayaran piutang).
- **Tahap 3:** View Blade & Livewire seluruh modul sesuai Enterprise Design System VOGILE (layout sidebar & topbar, dashboard per role, form transaksi) + Modul Laporan (`/laporan/penjualan`, `/laporan/pembelian`, `/laporan/laba-rugi`, `/laporan/neraca`, `/stok-card` persis sesuai desain referensi) + Ekspor PDF & Excel.
- **Tahap 4:** OCR AI (`/ocr/upload`, Queue Job `ProcessOcrScan`, driver, layar review split-view) + Fraud AI (rule engine F-01 s.d. F-12, scheduler, event listener, Dashboard Fraud Owner `/owner/fraud` dengan drawer tinjauan).
  </execution_plan>
  buatin projek laravelnya sampai dengan migration dan mvc nya sampai bisa dijalankan aplikasinya sesuai dengan prd dan rancangan database serta class yang saya kirim
