<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Jurnal Umum Penjualan
        Schema::create('ju_penjualan', function (Blueprint $table) {
            $table->string('id_penjualan', 30);
            $table->integer('kode_akun');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');

            $table->primary(['id_penjualan', 'kode_akun']);
            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualan')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('cascade');
        });

        // 2. Jurnal Umum Retur Penjualan
        Schema::create('ju_retur_penj', function (Blueprint $table) {
            $table->string('no_retur_penjualan', 30);
            $table->integer('kode_akun');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');

            $table->primary(['no_retur_penjualan', 'kode_akun']);
            $table->foreign('no_retur_penjualan')->references('no_retur_penjualan')->on('retur_penjualan')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('cascade');
        });

        // 3. Jurnal Umum Pelunasan Piutang
        Schema::create('ju_piutang', function (Blueprint $table) {
            $table->string('no_pelunasan', 30);
            $table->integer('kode_akun');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');

            $table->primary(['no_pelunasan', 'kode_akun']);
            $table->foreign('no_pelunasan')->references('no_pelunasan')->on('pelunasan_piutang')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('cascade');
        });

        // 4. Jurnal Umum Pembelian
        Schema::create('ju_pembelian', function (Blueprint $table) {
            $table->string('nota_pembelian', 30);
            $table->integer('kode_akun');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');

            $table->primary(['nota_pembelian', 'kode_akun']);
            $table->foreign('nota_pembelian')->references('nota_pembelian')->on('pembelian')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('cascade');
        });

        // 5. Jurnal Umum Retur Pembelian
        Schema::create('ju_retur_pemb', function (Blueprint $table) {
            $table->string('no_retur', 30);
            $table->integer('kode_akun');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');

            $table->primary(['no_retur', 'kode_akun']);
            $table->foreign('no_retur')->references('no_retur')->on('retur_pembelian')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('cascade');
        });

        // 6. Jurnal Umum Pembayaran Hutang
        Schema::create('ju_hutang', function (Blueprint $table) {
            $table->string('no_pembayaran', 30);
            $table->integer('kode_akun');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');

            $table->primary(['no_pembayaran', 'kode_akun']);
            $table->foreign('no_pembayaran')->references('no_pembayaran')->on('pembayaran_hutang')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('cascade');
        });

        // 7. Jurnal Umum Beban
        Schema::create('ju_beban', function (Blueprint $table) {
            $table->string('id_beban', 20);
            $table->integer('kode_akun');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->date('tanggal');

            $table->primary(['id_beban', 'kode_akun']);
            $table->foreign('id_beban')->references('id_beban')->on('beban')->onDelete('cascade');
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ju_beban');
        Schema::dropIfExists('ju_hutang');
        Schema::dropIfExists('ju_retur_pemb');
        Schema::dropIfExists('ju_pembelian');
        Schema::dropIfExists('ju_piutang');
        Schema::dropIfExists('ju_retur_penj');
        Schema::dropIfExists('ju_penjualan');
    }
};
