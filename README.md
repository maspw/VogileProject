# Vogile Enterprise

Sistem informasi akuntansi & distribusi bahan pokok untuk **PT VOGILE Distribusi**.
Dibangun dengan Laravel 12, Filament 3, MySQL, dan Tailwind CSS (via CDN pada layout internal).

## Fitur Utama

- **Master data**: barang (SKU), pelanggan dengan limit piutang, supplier, dan chart of accounts (COA).
- **Pembelian**: purchase order, nota pembelian (tunai/kredit), retur pembelian, pembayaran hutang.
- **Penjualan**: faktur penjualan (tunai/kredit), retur penjualan, pelunasan piutang, validasi limit piutang.
- **Beban operasional** dengan akun COA dan sumber dana (Kas / Bank BCA).
- **Persediaan**: kartu stok perpetual dengan HPP moving average.
- **Akuntansi**: jurnal umum otomatis dan seimbang untuk setiap transaksi terverifikasi, plus view `v_jurnal_umum`.
- **Kontrol internal**: pemisahan tugas (pembuat tidak boleh memverifikasi transaksi sendiri, kecuali Owner), fraud alert.
- **Dokumen PDF**: faktur penjualan, nota pembelian, retur, bukti pelunasan/pembayaran.
- **Filament Admin Panel** di `/admin` dengan otorisasi berbasis role.

## Kebutuhan

- PHP 8.2+ (ekstensi `pdo_mysql`, `mbstring`, `zip`, `gd`/`imagick` untuk dompdf)
- Composer 2
- MySQL/MariaDB (XAMPP) — proyek ini dikonfigurasi untuk port `3307`
- Node.js hanya diperlukan jika ingin mengompilasi aset Vite (UI utama memakai Tailwind CDN)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env` (nama database default: `db_vogile`), lalu:

```bash
php artisan migrate --seed
php artisan storage:link
```

Jalankan aplikasi:

```bash
php artisan serve
# atau akses http://localhost/Vogile-Project bila memakai XAMPP
```

## Akun Demo (hasil seeder)

Semua akun memakai password `password123`.

| Username | Email | Role |
| --- | --- | --- |
| `admin` | admin@vogile.com | Owner (super admin) |
| `owner` | owner@vogile.com | Owner |
| `sales` | sales@vogile.com | Sales |
| `adminpenjualan` | adminpenjualan@vogile.com | Admin Penjualan |
| `adminpiutang` | adminpiutang@vogile.com | Admin Piutang |
| `adminpembelian` | adminpembelian@vogile.com | Admin Pembelian |
| `adminhutang` | adminhutang@vogile.com | Admin Hutang |
| `kasir` | kasir@vogile.com | Kasir |

## Alur Kerja Transaksi

1. Transaksi dibuat dengan status **Menunggu** (tidak mengubah stok maupun jurnal).
2. Verifikasi oleh pengguna lain (Owner atau role terkait) memicu:
   - mutasi kartu stok & update stok barang,
   - posting jurnal umum yang divalidasi seimbang (`Σdebit = Σkredit`).
3. Retur hanya bisa dilakukan atas transaksi **Terverifikasi**, dibatasi sisa kuantitas yang belum diretur.
4. Pelunasan/pembayaran hanya untuk faktur **Kredit** terverifikasi, dibatasi sisa saldo yang tersedia.
5. Penjualan kredit divalidasi terhadap limit piutang pelanggan.

Kode akun standar: Kas 1101, Bank BCA 1102, Piutang Usaha 1103, Persediaan 1104,
Hutang Usaha 2101, Pendapatan Penjualan 4101, Retur Penjualan 4102, HPP 5101, Beban 61xx.

Nomor dokumen dibuat otomatis dengan format `PREFIKS-YYYYMM-XXX`
(mis. `PJ-202609-001`) melalui `App\Services\DocumentNumberGenerator`.

## Halaman Internal

| Modul | URL |
| --- | --- |
| Dashboard | `/dashboard` |
| Barang / Pelanggan / Supplier / COA | `/barang`, `/pelanggan`, `/supplier`, `/coa` |
| Purchase Order | `/po` |
| Pembelian / Retur / Pembayaran Hutang | `/pembelian`, `/retur-pembelian`, `/pembayaran-hutang` |
| Penjualan / Retur / Pelunasan Piutang | `/penjualan`, `/retur-penjualan`, `/pelunasan-piutang` |
| Beban Operasional | `/beban` |
| Filament Admin Panel | `/admin` |

Akses tiap modul dibatasi middleware `role:` sesuai tanggung jawab masing-masing role;
Owner (dan `U000`) memiliki akses penuh. Resource transaksi di Filament bersifat read-only
agar semua mutasi tetap melewati controller (stok, jurnal, dan validasi).

## Pengujian

```bash
php artisan test
```

Test menggunakan SQLite in-memory (lihat `phpunit.xml`) dan mencakup autentikasi,
otorisasi per role, pemisahan tugas, validasi pelunasan/retur, smoke test seluruh halaman,
serta rekonsiliasi data seeder (saldo stok = mutasi kartu stok terakhir, jurnal seimbang).

## Catatan Keamanan

- `.env` berisi `APP_KEY` dan kredensial database; jangan di-commit.
- Login dibatasi rate limit 5 percobaan/menit.
- Validasi server-side diterapkan pada seluruh input transaksi.
