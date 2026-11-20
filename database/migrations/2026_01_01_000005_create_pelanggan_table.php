<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('id_pelanggan', 20)->primary();
            $table->string('nama', 100);
            $table->text('alamat')->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->decimal('limit_piutang', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
