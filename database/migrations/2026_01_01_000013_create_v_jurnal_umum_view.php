<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_jurnal_umum");

        DB::statement("
            CREATE VIEW v_jurnal_umum AS
            SELECT 
                ju.id_penjualan AS id_sumber,
                'Penjualan' AS tipe_transaksi,
                ju.tanggal,
                ju.kode_akun,
                c.nama_akun,
                ju.debit,
                ju.kredit,
                ju.keterangan
            FROM ju_penjualan ju
            JOIN coa c ON ju.kode_akun = c.kode_akun

            UNION ALL

            SELECT 
                ju.no_retur_penjualan AS id_sumber,
                'Retur Penjualan' AS tipe_transaksi,
                ju.tanggal,
                ju.kode_akun,
                c.nama_akun,
                ju.debit,
                ju.kredit,
                ju.keterangan
            FROM ju_retur_penj ju
            JOIN coa c ON ju.kode_akun = c.kode_akun

            UNION ALL

            SELECT 
                ju.no_pelunasan AS id_sumber,
                'Pelunasan Piutang' AS tipe_transaksi,
                ju.tanggal,
                ju.kode_akun,
                c.nama_akun,
                ju.debit,
                ju.kredit,
                ju.keterangan
            FROM ju_piutang ju
            JOIN coa c ON ju.kode_akun = c.kode_akun

            UNION ALL

            SELECT 
                ju.nota_pembelian AS id_sumber,
                'Pembelian' AS tipe_transaksi,
                ju.tanggal,
                ju.kode_akun,
                c.nama_akun,
                ju.debit,
                ju.kredit,
                ju.keterangan
            FROM ju_pembelian ju
            JOIN coa c ON ju.kode_akun = c.kode_akun

            UNION ALL

            SELECT 
                ju.no_retur AS id_sumber,
                'Retur Pembelian' AS tipe_transaksi,
                ju.tanggal,
                ju.kode_akun,
                c.nama_akun,
                ju.debit,
                ju.kredit,
                ju.keterangan
            FROM ju_retur_pemb ju
            JOIN coa c ON ju.kode_akun = c.kode_akun

            UNION ALL

            SELECT 
                ju.no_pembayaran AS id_sumber,
                'Pembayaran Hutang' AS tipe_transaksi,
                ju.tanggal,
                ju.kode_akun,
                c.nama_akun,
                ju.debit,
                ju.kredit,
                ju.keterangan
            FROM ju_hutang ju
            JOIN coa c ON ju.kode_akun = c.kode_akun

            UNION ALL

            SELECT 
                ju.id_beban AS id_sumber,
                'Beban Operasional' AS tipe_transaksi,
                ju.tanggal,
                ju.kode_akun,
                c.nama_akun,
                ju.debit,
                ju.kredit,
                ju.keterangan
            FROM ju_beban ju
            JOIN coa c ON ju.kode_akun = c.kode_akun
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_jurnal_umum");
    }
};
