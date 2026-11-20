<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->string('nota_pembelian', 30)->primary();
            $table->date('tanggal');
            $table->string('id_supplier', 20)->nullable();
            $table->string('id_user', 20)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->string('metode_pembayaran', 20)->default('Tunai'); // 'Tunai' atau 'Kredit'
            $table->date('jatuh_tempo')->nullable();
            $table->string('status_verifikasi', 20)->default('Menunggu'); // 'Menunggu', 'Terverifikasi', 'Ditolak'
            $table->string('verified_by', 20)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('no_po', 30)->nullable();

            $table->foreign('id_supplier')->references('id_supplier')->on('supplier')->onDelete('set null');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('no_po')->references('no_po')->on('purchase_order')->onDelete('set null');
        });

        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->string('kode_barang', 30);
            $table->string('nota_pembelian', 30);
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->primary(['nota_pembelian', 'kode_barang']);
            $table->foreign('nota_pembelian')->references('nota_pembelian')->on('pembelian')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
        });

        Schema::create('retur_pembelian', function (Blueprint $table) {
            $table->string('no_retur', 30)->primary();
            $table->string('nota_pembelian', 30);
            $table->date('tanggal');
            $table->string('alasan', 255);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('id_user', 20)->nullable();
            $table->string('status_verifikasi', 20)->default('Menunggu');
            $table->string('verified_by', 20)->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->foreign('nota_pembelian')->references('nota_pembelian')->on('pembelian')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id_user')->on('users')->onDelete('set null');
        });

        Schema::create('detail_retur_pembelian', function (Blueprint $table) {
            $table->string('no_retur', 30);
            $table->string('kode_barang', 30);
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->primary(['no_retur', 'kode_barang']);
            $table->foreign('no_retur')->references('no_retur')->on('retur_pembelian')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
        });

        Schema::create('pembayaran_hutang', function (Blueprint $table) {
            $table->string('no_pembayaran', 30)->primary();
            $table->string('nota_pembelian', 30);
            $table->date('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('id_user', 20)->nullable();
            $table->string('status_verifikasi', 20)->default('Menunggu');
            $table->string('verified_by', 20)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan')->nullable();

            $table->foreign('nota_pembelian')->references('nota_pembelian')->on('pembelian')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_hutang');
        Schema::dropIfExists('detail_retur_pembelian');
        Schema::dropIfExists('retur_pembelian');
        Schema::dropIfExists('detail_pembelian');
        Schema::dropIfExists('pembelian');
    }
};
