<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->string('kode_barang', 30)->primary();
            $table->string('nama_barang', 150);
            $table->integer('jumlah_barang')->default(0);
            $table->decimal('harga_barang', 15, 2)->default(0);
            $table->decimal('hpp', 15, 2)->default(0);
            $table->string('satuan', 20)->default('Pcs');
            $table->integer('min_stok')->default(10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
