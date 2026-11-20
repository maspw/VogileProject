<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beban', function (Blueprint $table) {
            $table->string('id_beban', 20)->primary();
            $table->string('nama_beban', 100);
            $table->date('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('id_user', 20)->nullable();
            $table->string('status_verifikasi', 20)->default('Menunggu');
            $table->string('verified_by', 20)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan')->nullable();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('verified_by')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beban');
    }
};
