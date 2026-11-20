<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->string('no_po', 30)->primary();
            $table->date('tanggal');
            $table->string('id_supplier', 20);
            $table->string('id_user', 20)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status', 20)->default('Draf'); // Draf, Disetujui, Diproses, Selesai, Dibatalkan
            $table->text('catatan')->nullable();

            $table->foreign('id_supplier')->references('id_supplier')->on('supplier')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
        });

        Schema::create('detail_po', function (Blueprint $table) {
            $table->string('no_po', 30);
            $table->string('kode_barang', 30);
            $table->integer('jumlah')->default(1);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->primary(['no_po', 'kode_barang']);
            $table->foreign('no_po')->references('no_po')->on('purchase_order')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_po');
        Schema::dropIfExists('purchase_order');
    }
};
