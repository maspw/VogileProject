<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->string('id_penjualan', 30)->primary();
            $table->date('tanggal');
            $table->string('id_pelanggan', 20)->nullable();
            $table->string('id_user', 20)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->string('metode_pembayaran', 20)->default('Tunai'); // 'Tunai' atau 'Kredit'
            $table->date('jatuh_tempo')->nullable();
            $table->string('status_verifikasi', 20)->default('Menunggu'); // 'Menunggu', 'Terverifikasi', 'Ditolak'
            $table->string('verified_by', 20)->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('set null');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id_user')->on('users')->onDelete('set null');
        });

        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->string('kode_barang', 30);
            $table->string('id_penjualan', 30);
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->primary(['id_penjualan', 'kode_barang']);
            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualan')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
        });

        Schema::create('retur_penjualan', function (Blueprint $table) {
            $table->string('no_retur_penjualan', 30)->primary();
            $table->string('id_penjualan', 30);
            $table->date('tanggal');
            $table->string('alasan', 255);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('id_user', 20)->nullable();
            $table->string('status_verifikasi', 20)->default('Menunggu');
            $table->string('verified_by', 20)->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualan')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id_user')->on('users')->onDelete('set null');
        });

        Schema::create('detail_retur_penjualan', function (Blueprint $table) {
            $table->string('no_retur_penjualan', 30);
            $table->string('kode_barang', 30);
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->primary(['no_retur_penjualan', 'kode_barang']);
            $table->foreign('no_retur_penjualan')->references('no_retur_penjualan')->on('retur_penjualan')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
        });

        Schema::create('pelunasan_piutang', function (Blueprint $table) {
            $table->string('no_pelunasan', 30)->primary();
            $table->string('id_penjualan', 30);
            $table->date('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('id_user', 20)->nullable();
            $table->string('status_verifikasi', 20)->default('Menunggu');
            $table->string('verified_by', 20)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan')->nullable();

            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualan')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelunasan_piutang');
        Schema::dropIfExists('detail_retur_penjualan');
        Schema::dropIfExists('retur_penjualan');
        Schema::dropIfExists('detail_penjualan');
        Schema::dropIfExists('penjualan');
    }
};
