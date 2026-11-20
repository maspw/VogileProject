<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table) {
            $table->integer('kode_akun')->primary();
            $table->string('nama_akun', 100);
            $table->string('kategori', 50);
            $table->string('saldo_normal', 10); // 'Debit' atau 'Kredit'
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
