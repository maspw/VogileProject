<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Barang;
use App\Models\Beban;
use App\Models\Coa;
use App\Models\DetailPembelian;
use App\Models\DetailPenjualan;
use App\Models\DetailPo;
use App\Models\DetailReturPembelian;
use App\Models\DetailReturPenjualan;
use App\Models\FraudAlert;
use App\Models\JuBeban;
use App\Models\JuHutang;
use App\Models\JuPembelian;
use App\Models\JuPiutang;
use App\Models\JuPenjualan;
use App\Models\JuReturPemb;
use App\Models\JuReturPenj;
use App\Models\KartuStok;
use App\Models\Pelanggan;
use App\Models\PelunasanPiutang;
use App\Models\PembayaranHutang;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\PurchaseOrder;
use App\Models\ReturPembelian;
use App\Models\ReturPenjualan;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedRolesAndUsers();
            $this->seedCoa();
            $this->seedBarangAndKartuStok();
            $this->seedSuppliers();
            $this->seedPelanggan();
            $this->seedPurchaseOrderAndPembelian();
            $this->seedPenjualanAndPiutang();
            $this->seedStockMovements();
            $this->seedBebanOperasional();
            $this->seedFraudAnomalies();
        });
    }

    private function seedRolesAndUsers(): void
    {
        $roles = [
            ['id_role' => 1, 'nama_role' => 'Owner', 'no_telp' => '081122334401'],
            ['id_role' => 2, 'nama_role' => 'Sales', 'no_telp' => '081122334402'],
            ['id_role' => 3, 'nama_role' => 'Admin Penjualan', 'no_telp' => '081122334403'],
            ['id_role' => 4, 'nama_role' => 'Admin Piutang', 'no_telp' => '081122334404'],
            ['id_role' => 5, 'nama_role' => 'Admin Pembelian', 'no_telp' => '081122334405'],
            ['id_role' => 6, 'nama_role' => 'Admin Hutang', 'no_telp' => '081122334406'],
            ['id_role' => 7, 'nama_role' => 'Kasir', 'no_telp' => '081122334407'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id_role' => $r['id_role']], $r);
        }

        $users = [
            [
                'id_user' => 'U000',
                'name' => 'Super Admin',
                'nama_user' => 'Super Admin Vogile',
                'username' => 'admin',
                'email' => 'admin@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 1,
            ],
            [
                'id_user' => 'U001',
                'name' => 'Rafi Priyadi',
                'nama_user' => 'Rafi Priyadi (Owner)',
                'username' => 'owner',
                'email' => 'owner@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 1,
            ],
            [
                'id_user' => 'U002',
                'name' => 'Budi Santoso',
                'nama_user' => 'Budi Santoso (Sales)',
                'username' => 'sales',
                'email' => 'sales@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 2,
            ],
            [
                'id_user' => 'U003',
                'name' => 'Siti Aminah',
                'nama_user' => 'Siti Aminah (Admin Penjualan)',
                'username' => 'adminpenjualan',
                'email' => 'adminpenjualan@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 3,
            ],
            [
                'id_user' => 'U004',
                'name' => 'Dewi Lestari',
                'nama_user' => 'Dewi Lestari (Admin Piutang)',
                'username' => 'adminpiutang',
                'email' => 'adminpiutang@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 4,
            ],
            [
                'id_user' => 'U005',
                'name' => 'Ahmad Fauzi',
                'nama_user' => 'Ahmad Fauzi (Admin Pembelian)',
                'username' => 'adminpembelian',
                'email' => 'adminpembelian@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 5,
            ],
            [
                'id_user' => 'U006',
                'name' => 'Rina Wijaya',
                'nama_user' => 'Rina Wijaya (Admin Hutang)',
                'username' => 'adminhutang',
                'email' => 'adminhutang@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 6,
            ],
            [
                'id_user' => 'U007',
                'name' => 'Eko Prasetyo',
                'nama_user' => 'Eko Prasetyo (Kasir)',
                'username' => 'kasir',
                'email' => 'kasir@vogile.com',
                'password' => Hash::make('password123'),
                'id_role' => 7,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }
    }

    private function seedCoa(): void
    {
        $coaList = [
            ['kode_akun' => 1101, 'nama_akun' => 'Kas di Tangan', 'kategori' => 'Aset Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 1102, 'nama_akun' => 'Bank BCA', 'kategori' => 'Aset Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 1103, 'nama_akun' => 'Piutang Usaha', 'kategori' => 'Aset Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 1104, 'nama_akun' => 'Persediaan Barang Dagang', 'kategori' => 'Aset Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 1105, 'nama_akun' => 'Perlengkapan Toko & Gudang', 'kategori' => 'Aset Lancar', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 2101, 'nama_akun' => 'Hutang Usaha', 'kategori' => 'Kewajiban Lancar', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => 3101, 'nama_akun' => 'Modal Pemilik PT VOGILE', 'kategori' => 'Ekuitas', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => 4101, 'nama_akun' => 'Pendapatan Penjualan', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
            ['kode_akun' => 4102, 'nama_akun' => 'Retur & Potongan Penjualan', 'kategori' => 'Pendapatan', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 5101, 'nama_akun' => 'Harga Pokok Penjualan (HPP)', 'kategori' => 'Beban Pokok', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 6101, 'nama_akun' => 'Beban Gaji & Upah Karyawan', 'kategori' => 'Beban Operasional', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 6102, 'nama_akun' => 'Beban Listrik, Air & Internet', 'kategori' => 'Beban Operasional', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 6103, 'nama_akun' => 'Beban Sewa Gedung & Gudang', 'kategori' => 'Beban Operasional', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 6104, 'nama_akun' => 'Beban Transportasi & Pengiriman', 'kategori' => 'Beban Operasional', 'saldo_normal' => 'Debit'],
            ['kode_akun' => 6105, 'nama_akun' => 'Beban Pemeliharaan & Operasional', 'kategori' => 'Beban Operasional', 'saldo_normal' => 'Debit'],
        ];

        foreach ($coaList as $c) {
            Coa::updateOrCreate(['kode_akun' => $c['kode_akun']], $c);
        }
    }

    private function seedBarangAndKartuStok(): void
    {
        $barangs = [
            ['kode_barang' => 'BRG001', 'nama_barang' => 'Minyak Goreng Sawit 2 Liter', 'jumlah_barang' => 200, 'harga_barang' => 36000, 'hpp' => 30000, 'satuan' => 'Pch', 'min_stok' => 25],
            ['kode_barang' => 'BRG002', 'nama_barang' => 'Beras Premium Ramos 5 Kg', 'jumlah_barang' => 150, 'harga_barang' => 78000, 'hpp' => 65000, 'satuan' => 'Sak', 'min_stok' => 20],
            ['kode_barang' => 'BRG003', 'nama_barang' => 'Gula Pasir Kristal Putih 1 Kg', 'jumlah_barang' => 300, 'harga_barang' => 17500, 'hpp' => 14000, 'satuan' => 'Bks', 'min_stok' => 30],
            ['kode_barang' => 'BRG004', 'nama_barang' => 'Tepung Terigu Serbaguna 1 Kg', 'jumlah_barang' => 250, 'harga_barang' => 13000, 'hpp' => 10500, 'satuan' => 'Bks', 'min_stok' => 25],
            ['kode_barang' => 'BRG005', 'nama_barang' => 'Kopi Bubuk Robusta Asli 250g', 'jumlah_barang' => 180, 'harga_barang' => 23000, 'hpp' => 18000, 'satuan' => 'Bks', 'min_stok' => 20],
            ['kode_barang' => 'BRG006', 'nama_barang' => 'Susu Kental Manis Putih 370g', 'jumlah_barang' => 220, 'harga_barang' => 14000, 'hpp' => 11000, 'satuan' => 'Klg', 'min_stok' => 20],
            ['kode_barang' => 'BRG007', 'nama_barang' => 'Teh Celup Melati Isi 25 Kotak', 'jumlah_barang' => 140, 'harga_barang' => 10000, 'hpp' => 7500, 'satuan' => 'Ktk', 'min_stok' => 15],
            ['kode_barang' => 'BRG008', 'nama_barang' => 'Mie Instan Rasa Ayam Bawang (Dus)', 'jumlah_barang' => 120, 'harga_barang' => 125000, 'hpp' => 105000, 'satuan' => 'Dus', 'min_stok' => 15],
            ['kode_barang' => 'BRG009', 'nama_barang' => 'Kecap Manis Botol 600 ml', 'jumlah_barang' => 160, 'harga_barang' => 24000, 'hpp' => 19000, 'satuan' => 'Btl', 'min_stok' => 20],
            ['kode_barang' => 'BRG010', 'nama_barang' => 'Saus Sambal Ekstra Pedas 335 ml', 'jumlah_barang' => 110, 'harga_barang' => 16000, 'hpp' => 12500, 'satuan' => 'Btl', 'min_stok' => 15],
        ];

        foreach ($barangs as $b) {
            Barang::updateOrCreate(['kode_barang' => $b['kode_barang']], $b);

            KartuStok::updateOrCreate(
                [
                    'kode_barang' => $b['kode_barang'],
                    'no_referensi' => 'OPN-20260901',
                ],
                [
                    'tanggal' => '2026-09-01 08:00:00',
                    'jenis_transaksi' => 'Saldo Awal',
                    'masuk' => $b['jumlah_barang'],
                    'keluar' => 0,
                    'saldo' => $b['jumlah_barang'],
                    'harga_satuan' => $b['hpp'],
                    'keterangan' => 'Opname Fisik Saldo Awal Gudang',
                ]
            );
        }
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            ['id_supplier' => 'SUP001', 'nama_supplier' => 'PT Indofood Sukses Makmur Tbk', 'alamat' => 'Kawasan Industri Pulogadung Blok A2, Jakarta', 'no_telp' => '021-4600101', 'email' => 'sales@indofood.co.id'],
            ['id_supplier' => 'SUP002', 'nama_supplier' => 'PT Wilmar Nabati Indonesia', 'alamat' => 'Gedung BII Plaza Lt. 7, Surabaya', 'no_telp' => '031-5347890', 'email' => 'contact@wilmar.co.id'],
            ['id_supplier' => 'SUP003', 'nama_supplier' => 'PT Mayora Indah Tbk', 'alamat' => 'Jl. Tomang Raya No. 21-23, Jakarta Barat', 'no_telp' => '021-5655320', 'email' => 'order@mayora.co.id'],
            ['id_supplier' => 'SUP004', 'nama_supplier' => 'PT Unilever Indonesia Tbk', 'alamat' => 'Grha Unilever, BSD City Tangerang', 'no_telp' => '021-8082700', 'email' => 'distribusi@unilever.com'],
            ['id_supplier' => 'SUP005', 'nama_supplier' => 'CV Berkah Pangan Nusantara', 'alamat' => 'Jl. Raya Cirebon - Bandung KM 12', 'no_telp' => '0231-897123', 'email' => 'berkahpangan@gmail.com'],
        ];

        foreach ($suppliers as $s) {
            Supplier::updateOrCreate(['id_supplier' => $s['id_supplier']], $s);
        }
    }

    private function seedPelanggan(): void
    {
        $pelangganList = [
            ['id_pelanggan' => 'PLG001', 'nama' => 'Toko Kelontong Berkah Ibu', 'alamat' => 'Pasar Induk Kramat Jati Kios B-12', 'no_telp' => '081234567891', 'limit_piutang' => 25000000],
            ['id_pelanggan' => 'PLG002', 'nama' => 'Minimarket Surya Jaya', 'alamat' => 'Jl. Merdeka No. 45 Jakarta Timur', 'no_telp' => '081234567892', 'limit_piutang' => 40000000],
            ['id_pelanggan' => 'PLG003', 'nama' => 'Toko Sembako Makmur Abadi', 'alamat' => 'Jl. Pahlawan No. 89 Bekasi', 'no_telp' => '081234567893', 'limit_piutang' => 20000000],
            ['id_pelanggan' => 'PLG004', 'nama' => 'Warung Barokah Madura 24 Jam', 'alamat' => 'Jl. Raya Bogor KM 28 Depok', 'no_telp' => '081234567894', 'limit_piutang' => 10000000],
            ['id_pelanggan' => 'PLG005', 'nama' => 'Koperasi Pegawai Sejahtera Mandiri', 'alamat' => 'Komplek Perkantoran Pemda Cikarang', 'no_telp' => '081234567895', 'limit_piutang' => 30000000],
            ['id_pelanggan' => 'PLG006', 'nama' => 'Toko Grosir Subur Jaya', 'alamat' => 'Pasar Baru Cikarang Blok C No. 5', 'no_telp' => '081234567896', 'limit_piutang' => 50000000],
            ['id_pelanggan' => 'PLG007', 'nama' => 'Ceria Mart Sukatani', 'alamat' => 'Jl. Sukatani Raya No. 12', 'no_telp' => '081234567897', 'limit_piutang' => 15000000],
            ['id_pelanggan' => 'PLG008', 'nama' => 'Warung Pojok Bu Endang', 'alamat' => 'Jl. Kenanga No. 3 Tambun Selatan', 'no_telp' => '081234567898', 'limit_piutang' => 15000000],
        ];

        foreach ($pelangganList as $p) {
            Pelanggan::updateOrCreate(['id_pelanggan' => $p['id_pelanggan']], $p);
        }
    }

    private function seedPurchaseOrderAndPembelian(): void
    {
        // 1. PO-202609-001
        $po = PurchaseOrder::updateOrCreate(
            ['no_po' => 'PO-202609-001'],
            [
                'tanggal' => '2026-09-05',
                'id_supplier' => 'SUP002',
                'id_user' => 'U005',
                'total' => 6000000,
                'status' => 'Selesai',
                'catatan' => 'Restock Minyak Goreng Sawit 200 pouch',
            ]
        );

        DetailPo::updateOrCreate(
            ['no_po' => 'PO-202609-001', 'kode_barang' => 'BRG001'],
            ['jumlah' => 200, 'harga_satuan' => 30000, 'subtotal' => 6000000]
        );

        // Pembelian Kredit PB-202609-001
        Pembelian::updateOrCreate(
            ['nota_pembelian' => 'PB-202609-001'],
            [
                'tanggal' => '2026-09-07',
                'id_supplier' => 'SUP002',
                'id_user' => 'U005',
                'total' => 6000000,
                'metode_pembayaran' => 'Kredit',
                'jatuh_tempo' => '2026-10-07',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U001',
                'verified_at' => '2026-09-07 14:00:00',
                'no_po' => 'PO-202609-001',
            ]
        );

        DetailPembelian::updateOrCreate(
            ['nota_pembelian' => 'PB-202609-001', 'kode_barang' => 'BRG001'],
            ['jumlah' => 200, 'harga_satuan' => 30000, 'subtotal' => 6000000]
        );

        // Jurnal Pembelian Kredit: (D) Persediaan 1104, (K) Hutang Usaha 2101
        JuPembelian::updateOrCreate(
            ['nota_pembelian' => 'PB-202609-001', 'kode_akun' => 1104],
            ['debit' => 6000000, 'kredit' => 0, 'keterangan' => 'Pembelian kredit persediaan minyak goreng', 'tanggal' => '2026-09-07']
        );
        JuPembelian::updateOrCreate(
            ['nota_pembelian' => 'PB-202609-001', 'kode_akun' => 2101],
            ['debit' => 0, 'kredit' => 6000000, 'keterangan' => 'Hutang usaha atas pembelian minyak goreng', 'tanggal' => '2026-09-07']
        );

        // Retur Sebagian Pembelian: RTB-202609-001 (10 pouch bocor = 300.000)
        ReturPembelian::updateOrCreate(
            ['no_retur' => 'RTB-202609-001'],
            [
                'nota_pembelian' => 'PB-202609-001',
                'tanggal' => '2026-09-09',
                'alasan' => 'Kemasan 10 pouch bocor saat pengiriman',
                'total' => 300000,
                'id_user' => 'U005',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U001',
                'verified_at' => '2026-09-09 16:00:00',
            ]
        );

        DetailReturPembelian::updateOrCreate(
            ['no_retur' => 'RTB-202609-001', 'kode_barang' => 'BRG001'],
            ['jumlah' => 10, 'harga_satuan' => 30000, 'subtotal' => 300000]
        );

        // Jurnal Retur Pembelian: (D) Hutang Usaha 2101, (K) Persediaan 1104
        JuReturPemb::updateOrCreate(
            ['no_retur' => 'RTB-202609-001', 'kode_akun' => 2101],
            ['debit' => 300000, 'kredit' => 0, 'keterangan' => 'Pengurangan hutang retur pembelian minyak goreng', 'tanggal' => '2026-09-09']
        );
        JuReturPemb::updateOrCreate(
            ['no_retur' => 'RTB-202609-001', 'kode_akun' => 1104],
            ['debit' => 0, 'kredit' => 300000, 'keterangan' => 'Pengembalian persediaan minyak goreng rusak', 'tanggal' => '2026-09-09']
        );

        // Pembayaran Sebagian Hutang: PH-202609-001 (Bayar Rp 3.000.000 via BCA)
        PembayaranHutang::updateOrCreate(
            ['no_pembayaran' => 'PH-202609-001'],
            [
                'nota_pembelian' => 'PB-202609-001',
                'tanggal' => '2026-09-25',
                'total' => 3000000,
                'metode_bayar' => 'Bank BCA',
                'id_user' => 'U006',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U001',
                'verified_at' => '2026-09-25 11:00:00',
                'catatan' => 'Cicilan termin 1 via Transfer Bank BCA',
            ]
        );

        // Jurnal Pembayaran Hutang: (D) Hutang Usaha 2101, (K) Bank BCA 1102
        JuHutang::updateOrCreate(
            ['no_pembayaran' => 'PH-202609-001', 'kode_akun' => 2101],
            ['debit' => 3000000, 'kredit' => 0, 'keterangan' => 'Pembayaran hutang PB-202609-001 termin 1', 'tanggal' => '2026-09-25']
        );
        JuHutang::updateOrCreate(
            ['no_pembayaran' => 'PH-202609-001', 'kode_akun' => 1102],
            ['debit' => 0, 'kredit' => 3000000, 'keterangan' => 'Pengeluaran Bank BCA pelunasan hutang termin 1', 'tanggal' => '2026-09-25']
        );
    }

    private function seedPenjualanAndPiutang(): void
    {
        // 1. Penjualan Tunai PJ-202609-001 (Kasir)
        Penjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-001'],
            [
                'tanggal' => '2026-09-10',
                'id_pelanggan' => 'PLG004',
                'id_user' => 'U007',
                'total' => 720000,
                'metode_pembayaran' => 'Tunai',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U001',
                'verified_at' => '2026-09-10 15:00:00',
            ]
        );

        DetailPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-001', 'kode_barang' => 'BRG001'],
            ['jumlah' => 20, 'harga_satuan' => 36000, 'subtotal' => 720000]
        );

        // Jurnal Penjualan Tunai:
        // (D) Kas 1101 = 720.000, (K) Penjualan 4101 = 720.000
        // (D) HPP 5101 = 600.000, (K) Persediaan 1104 = 600.000
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-001', 'kode_akun' => 1101],
            ['debit' => 720000, 'kredit' => 0, 'keterangan' => 'Kas penjualan tunai warung barokah', 'tanggal' => '2026-09-10']
        );
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-001', 'kode_akun' => 4101],
            ['debit' => 0, 'kredit' => 720000, 'keterangan' => 'Pendapatan penjualan tunai', 'tanggal' => '2026-09-10']
        );
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-001', 'kode_akun' => 5101],
            ['debit' => 600000, 'kredit' => 0, 'keterangan' => 'Beban Pokok Penjualan atas faktur PJ-202609-001', 'tanggal' => '2026-09-10']
        );
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-001', 'kode_akun' => 1104],
            ['debit' => 0, 'kredit' => 600000, 'keterangan' => 'Pengurangan persediaan barang atas faktur PJ-202609-001', 'tanggal' => '2026-09-10']
        );

        // 2. Penjualan Kredit PJ-202609-002 (Toko Surya Jaya)
        Penjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-002'],
            [
                'tanggal' => '2026-09-15',
                'id_pelanggan' => 'PLG002',
                'id_user' => 'U003',
                'total' => 7800000,
                'metode_pembayaran' => 'Kredit',
                'jatuh_tempo' => '2026-10-15',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U001',
                'verified_at' => '2026-09-15 17:00:00',
            ]
        );

        DetailPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-002', 'kode_barang' => 'BRG002'],
            ['jumlah' => 100, 'harga_satuan' => 78000, 'subtotal' => 7800000]
        );

        // Jurnal Penjualan Kredit:
        // (D) Piutang Usaha 1103 = 7.800.000, (K) Penjualan 4101 = 7.800.000
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-002', 'kode_akun' => 1103],
            ['debit' => 7800000, 'kredit' => 0, 'keterangan' => 'Piutang usaha penjualan beras minimarket surya jaya', 'tanggal' => '2026-09-15']
        );
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-002', 'kode_akun' => 4101],
            ['debit' => 0, 'kredit' => 7800000, 'keterangan' => 'Pendapatan penjualan beras kredit', 'tanggal' => '2026-09-15']
        );
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-002', 'kode_akun' => 5101],
            ['debit' => 6500000, 'kredit' => 0, 'keterangan' => 'Beban Pokok Penjualan atas faktur PJ-202609-002', 'tanggal' => '2026-09-15']
        );
        JuPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-202609-002', 'kode_akun' => 1104],
            ['debit' => 0, 'kredit' => 6500000, 'keterangan' => 'Pengurangan persediaan barang atas faktur PJ-202609-002', 'tanggal' => '2026-09-15']
        );

        // Retur Penjualan: RTJ-202609-001 (2 sak beras kutuan = 156.000)
        ReturPenjualan::updateOrCreate(
            ['no_retur_penjualan' => 'RTJ-202609-001'],
            [
                'id_penjualan' => 'PJ-202609-002',
                'tanggal' => '2026-09-18',
                'alasan' => 'Dua sak beras terdapat kutu saat unboxing',
                'total' => 156000,
                'id_user' => 'U003',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U001',
                'verified_at' => '2026-09-18 10:00:00',
            ]
        );

        DetailReturPenjualan::updateOrCreate(
            ['no_retur_penjualan' => 'RTJ-202609-001', 'kode_barang' => 'BRG002'],
            ['jumlah' => 2, 'harga_satuan' => 78000, 'subtotal' => 156000]
        );

        // Jurnal Retur Penjualan: (D) Retur Penjualan 4102, (K) Piutang Usaha 1103
        JuReturPenj::updateOrCreate(
            ['no_retur_penjualan' => 'RTJ-202609-001', 'kode_akun' => 4102],
            ['debit' => 156000, 'kredit' => 0, 'keterangan' => 'Retur penjualan 2 sak beras dari Surya Jaya', 'tanggal' => '2026-09-18']
        );
        JuReturPenj::updateOrCreate(
            ['no_retur_penjualan' => 'RTJ-202609-001', 'kode_akun' => 1103],
            ['debit' => 0, 'kredit' => 156000, 'keterangan' => 'Pengurangan piutang retur penjualan beras Surya Jaya', 'tanggal' => '2026-09-18']
        );
        JuReturPenj::updateOrCreate(
            ['no_retur_penjualan' => 'RTJ-202609-001', 'kode_akun' => 1104],
            ['debit' => 130000, 'kredit' => 0, 'keterangan' => 'Pengembalian persediaan dari retur RTJ-202609-001', 'tanggal' => '2026-09-18']
        );
        JuReturPenj::updateOrCreate(
            ['no_retur_penjualan' => 'RTJ-202609-001', 'kode_akun' => 5101],
            ['debit' => 0, 'kredit' => 130000, 'keterangan' => 'Pengurangan HPP atas barang retur RTJ-202609-001', 'tanggal' => '2026-09-18']
        );

        // Pelunasan Sebagian Piutang: PP-202609-001 (Bayar Rp 4.000.000 via BCA)
        PelunasanPiutang::updateOrCreate(
            ['no_pelunasan' => 'PP-202609-001'],
            [
                'id_penjualan' => 'PJ-202609-002',
                'tanggal' => '2026-09-28',
                'total' => 4000000,
                'metode_bayar' => 'Bank BCA',
                'id_user' => 'U004',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U001',
                'verified_at' => '2026-09-28 14:00:00',
                'catatan' => 'Pelunasan piutang termin 1 via transfer BCA',
            ]
        );

        // Jurnal Pelunasan Piutang: (D) Bank BCA 1102, (K) Piutang Usaha 1103
        JuPiutang::updateOrCreate(
            ['no_pelunasan' => 'PP-202609-001', 'kode_akun' => 1102],
            ['debit' => 4000000, 'kredit' => 0, 'keterangan' => 'Penerimaan BCA pelunasan piutang Surya Jaya', 'tanggal' => '2026-09-28']
        );
        JuPiutang::updateOrCreate(
            ['no_pelunasan' => 'PP-202609-001', 'kode_akun' => 1103],
            ['debit' => 0, 'kredit' => 4000000, 'keterangan' => 'Pengurangan piutang Surya Jaya termin 1', 'tanggal' => '2026-09-28']
        );
    }

    private function seedStockMovements(): void
    {
        $movements = [
            [
                'kode_barang' => 'BRG001',
                'no_referensi' => 'PB-202609-001',
                'tanggal' => '2026-09-07 10:00:00',
                'jenis_transaksi' => 'Pembelian',
                'masuk' => 200,
                'keluar' => 0,
                'saldo' => 400,
                'harga_satuan' => 30000,
                'keterangan' => 'Penerimaan minyak goreng dari PT Wilmar Nabati Indonesia',
            ],
            [
                'kode_barang' => 'BRG001',
                'no_referensi' => 'RTB-202609-001',
                'tanggal' => '2026-09-09 16:00:00',
                'jenis_transaksi' => 'Retur Pembelian',
                'masuk' => 0,
                'keluar' => 10,
                'saldo' => 390,
                'harga_satuan' => 30000,
                'keterangan' => 'Pengembalian 10 pouch bocor ke supplier',
            ],
            [
                'kode_barang' => 'BRG001',
                'no_referensi' => 'PJ-202609-001',
                'tanggal' => '2026-09-10 15:00:00',
                'jenis_transaksi' => 'Penjualan',
                'masuk' => 0,
                'keluar' => 20,
                'saldo' => 370,
                'harga_satuan' => 36000,
                'keterangan' => 'Pengiriman barang ke Warung Barokah Madura 24 Jam',
            ],
            [
                'kode_barang' => 'BRG002',
                'no_referensi' => 'PJ-202609-002',
                'tanggal' => '2026-09-15 17:00:00',
                'jenis_transaksi' => 'Penjualan',
                'masuk' => 0,
                'keluar' => 100,
                'saldo' => 50,
                'harga_satuan' => 78000,
                'keterangan' => 'Pengiriman barang ke Minimarket Surya Jaya',
            ],
            [
                'kode_barang' => 'BRG002',
                'no_referensi' => 'RTJ-202609-001',
                'tanggal' => '2026-09-18 10:00:00',
                'jenis_transaksi' => 'Retur Penjualan',
                'masuk' => 2,
                'keluar' => 0,
                'saldo' => 52,
                'harga_satuan' => 78000,
                'keterangan' => 'Penerimaan kembali 2 sak beras dari Minimarket Surya Jaya',
            ],
        ];

        foreach ($movements as $m) {
            KartuStok::updateOrCreate(
                ['kode_barang' => $m['kode_barang'], 'no_referensi' => $m['no_referensi']],
                $m
            );
        }

        // Saldo akhir barang harus sama dengan mutasi terakhir pada kartu stok.
        Barang::updateOrCreate(['kode_barang' => 'BRG001'], ['jumlah_barang' => 370]);
        Barang::updateOrCreate(['kode_barang' => 'BRG002'], ['jumlah_barang' => 52]);
    }

    private function seedBebanOperasional(): void
    {
        $bebanData = [
            // Bulan September 2026
            [
                'id_beban' => 'BBN-202609-001',
                'nama_beban' => 'Beban Gaji Karyawan September 2026',
                'tanggal' => '2026-09-30',
                'total' => 18500000,
                'id_user' => 'U007',
                'kode_akun' => 6101,
            ],
            [
                'id_beban' => 'BBN-202609-002',
                'nama_beban' => 'Beban Listrik, Air & Wifi September',
                'tanggal' => '2026-09-30',
                'total' => 2400000,
                'id_user' => 'U007',
                'kode_akun' => 6102,
            ],
            // Bulan Oktober 2026
            [
                'id_beban' => 'BBN-202610-001',
                'nama_beban' => 'Beban Gaji Karyawan Oktober 2026',
                'tanggal' => '2026-10-31',
                'total' => 18500000,
                'id_user' => 'U007',
                'kode_akun' => 6101,
            ],
            [
                'id_beban' => 'BBN-202610-002',
                'nama_beban' => 'Beban Sewa Gudang Tambahan Oktober',
                'tanggal' => '2026-10-15',
                'total' => 5000000,
                'id_user' => 'U007',
                'kode_akun' => 6103,
            ],
            // Bulan November 2026
            [
                'id_beban' => 'BBN-202611-001',
                'nama_beban' => 'Beban Gaji Karyawan November 2026',
                'tanggal' => '2026-11-20',
                'total' => 18500000,
                'id_user' => 'U007',
                'kode_akun' => 6101,
            ],
        ];

        foreach ($bebanData as $b) {
            Beban::updateOrCreate(
                ['id_beban' => $b['id_beban']],
                [
                    'nama_beban' => $b['nama_beban'],
                    'tanggal' => $b['tanggal'],
                    'total' => $b['total'],
                    'kode_akun' => $b['kode_akun'],
                    'metode_bayar' => 'Bank BCA',
                    'id_user' => $b['id_user'],
                    'status_verifikasi' => 'Terverifikasi',
                    'verified_by' => 'U001',
                    'verified_at' => $b['tanggal'] . ' 17:00:00',
                    'catatan' => 'Pengeluaran rutin bulanan terverifikasi',
                ]
            );

            // Jurnal Beban: (D) Beban Terkait, (K) Kas/Bank 1102
            JuBeban::updateOrCreate(
                ['id_beban' => $b['id_beban'], 'kode_akun' => $b['kode_akun']],
                ['debit' => $b['total'], 'kredit' => 0, 'keterangan' => $b['nama_beban'], 'tanggal' => $b['tanggal']]
            );
            JuBeban::updateOrCreate(
                ['id_beban' => $b['id_beban'], 'kode_akun' => 1102],
                ['debit' => 0, 'kredit' => $b['total'], 'keterangan' => 'Pengeluaran Bank BCA untuk ' . $b['nama_beban'], 'tanggal' => $b['tanggal']]
            );
        }
    }

    private function seedFraudAnomalies(): void
    {
        // 1. Kasus Anomali F-01: Harga Jual di Bawah HPP
        // BRG001 HPP = Rp 30.000, dijual dengan harga Rp 22.000 (rugi Rp 8.000/item)
        Penjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-ANOMALI-001'],
            [
                'tanggal' => '2026-11-18',
                'id_pelanggan' => 'PLG001',
                'id_user' => 'U003',
                'total' => 1100000,
                'metode_pembayaran' => 'Kredit',
                'status_verifikasi' => 'Menunggu',
            ]
        );

        DetailPenjualan::updateOrCreate(
            ['id_penjualan' => 'PJ-ANOMALI-001', 'kode_barang' => 'BRG001'],
            ['jumlah' => 50, 'harga_satuan' => 22000, 'subtotal' => 1100000]
        );

        // Daftarkan Alert Fraud F-01
        FraudAlert::updateOrCreate(
            ['kode_aturan' => 'F-01', 'no_referensi' => 'PJ-ANOMALI-001'],
            [
                'kategori' => 'Pricing Anomaly',
                'tingkat_risiko' => 'Tinggi',
                'skor_risiko' => 85,
                'tabel_referensi' => 'penjualan',
                'deskripsi' => 'Harga jual Minyak Goreng Sawit (Rp 22.000) berada di bawah HPP (Rp 30.000). Potensi kerugian margin sebesar 26.7%.',
                'bukti_json' => [
                    'kode_barang' => 'BRG001',
                    'hpp' => 30000,
                    'harga_jual' => 22000,
                    'selisih_rugi' => -8000,
                    'qty' => 50,
                    'total_potensi_rugi' => 400000,
                ],
                'status' => 'Menunggu Review',
                'id_user_pelaku' => 'U003',
            ]
        );

        // 2. Kasus Anomali F-02: Beban Melonjak Drastis (> 300%)
        // Beban Transportasi melonjak drastis ke Rp 35.000.000
        Beban::updateOrCreate(
            ['id_beban' => 'BBN-ANOMALI-001'],
            [
                'nama_beban' => 'Beban Ekspedisi Khusus Luar Kota Darurat',
                'tanggal' => '2026-11-19',
                'total' => 35000000,
                'kode_akun' => 6104,
                'metode_bayar' => 'Kas',
                'id_user' => 'U007',
                'status_verifikasi' => 'Menunggu',
                'catatan' => 'Biaya ekspedisi tidak wajar tanpa lampiran PO/SPK resmi',
            ]
        );

        FraudAlert::updateOrCreate(
            ['kode_aturan' => 'F-02', 'no_referensi' => 'BBN-ANOMALI-001'],
            [
                'kategori' => 'Expense Spike',
                'tingkat_risiko' => 'Kritis',
                'skor_risiko' => 95,
                'tabel_referensi' => 'beban',
                'deskripsi' => 'Pengeluaran beban transportasi sebesar Rp 35.000.000 melonjak >450% dibandingkan rata-rata 3 bulan terakhir (Rp 6.200.000).',
                'bukti_json' => [
                    'nominal_saat_ini' => 35000000,
                    'rata_rata_sebelumnya' => 6200000,
                    'deviasi_persen' => 464.5,
                ],
                'status' => 'Menunggu Review',
                'id_user_pelaku' => 'U007',
            ]
        );

        // 3. Kasus Anomali F-03: Segregation of Duties Violation
        // Penginput = Pemverifikasi (U005 memverifikasi transaksinya sendiri)
        Pembelian::updateOrCreate(
            ['nota_pembelian' => 'PB-ANOMALI-001'],
            [
                'tanggal' => '2026-11-19',
                'id_supplier' => 'SUP005',
                'id_user' => 'U005',
                'total' => 12500000,
                'metode_pembayaran' => 'Tunai',
                'status_verifikasi' => 'Terverifikasi',
                'verified_by' => 'U005', // Pelanggaran: pembuat memverifikasi diri sendiri!
                'verified_at' => '2026-11-19 16:30:00',
            ]
        );

        FraudAlert::updateOrCreate(
            ['kode_aturan' => 'F-03', 'no_referensi' => 'PB-ANOMALI-001'],
            [
                'kategori' => 'Segregation of Duties',
                'tingkat_risiko' => 'Kritis',
                'skor_risiko' => 92,
                'tabel_referensi' => 'pembelian',
                'deskripsi' => 'Pelanggaran pemisahan kewenangan (Segregation of Duties): Ahmad Fauzi (Admin Pembelian) membuat dan memverifikasi nota pembelian PB-ANOMALI-001 sekaligus.',
                'bukti_json' => [
                    'id_user_pembuat' => 'U005',
                    'id_user_pemverifikasi' => 'U005',
                    'nota' => 'PB-ANOMALI-001',
                    'total' => 12500000,
                ],
                'status' => 'Menunggu Review',
                'id_user_pelaku' => 'U005',
            ]
        );

        // Catat di ActivityLog sekali saja agar seeder tetap idempoten
        $sudahDicatat = ActivityLog::where('id_user', 'U001')
            ->where('aktivitas', 'Inisialisasi Master Data & Audit Seeder')
            ->exists();

        if (! $sudahDicatat) {
            ActivityLog::create([
                'id_user' => 'U001',
                'aktivitas' => 'Inisialisasi Master Data & Audit Seeder',
                'modul' => 'Sistem / Setup',
                'data_lama' => null,
                'data_baru' => ['status' => 'DatabaseSeeder berhasil di-generate secara penuh'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Console / Artisan Seeder',
            ]);
        }
    }
}
