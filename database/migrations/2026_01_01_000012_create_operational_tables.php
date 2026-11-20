<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kartu Stok
        Schema::create('kartu_stok', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 30);
            $table->dateTime('tanggal');
            $table->string('jenis_transaksi', 50); // Saldo Awal, Pembelian, Penjualan, Retur Pembelian, Retur Penjualan, Penyesuaian
            $table->string('no_referensi', 50);
            $table->integer('masuk')->default(0);
            $table->integer('keluar')->default(0);
            $table->integer('saldo')->default(0);
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
            $table->index(['kode_barang', 'tanggal']);
        });

        // 2. OCR Scans
        Schema::create('ocr_scans', function (Blueprint $table) {
            $table->id();
            $table->string('id_user', 20)->nullable();
            $table->string('jenis_dokumen', 50); // Nota Pembelian, Faktur Penjualan, Kuitansi Beban
            $table->string('file_path');
            $table->json('hasil_json')->nullable();
            $table->string('status', 30)->default('Pending'); // Pending, Processing, Sukses, Gagal, Diterapkan
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
        });

        // 3. Fraud Alerts
        Schema::create('fraud_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aturan', 30); // F-01 s/d F-12
            $table->string('kategori', 50);
            $table->string('tingkat_risiko', 20)->default('Sedang'); // Rendah, Sedang, Tinggi, Kritis
            $table->integer('skor_risiko')->default(0); // 0 - 100
            $table->string('no_referensi', 50)->nullable();
            $table->string('tabel_referensi', 50)->nullable();
            $table->text('deskripsi');
            $table->json('bukti_json')->nullable();
            $table->string('status', 30)->default('Menunggu Review'); // Menunggu Review, Dikonfirmasi Fraud, Bukan Fraud, Diabaikan
            $table->string('id_user_pelaku', 20)->nullable();
            $table->string('id_user_reviewer', 20)->nullable();
            $table->text('catatan_review')->nullable();
            $table->timestamps();

            $table->foreign('id_user_pelaku')->references('id_user')->on('users')->onDelete('set null');
            $table->foreign('id_user_reviewer')->references('id_user')->on('users')->onDelete('set null');
        });

        // 4. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('id_user', 20)->nullable();
            $table->string('aktivitas', 100);
            $table->string('modul', 50);
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
            $table->index(['id_user', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('fraud_alerts');
        Schema::dropIfExists('ocr_scans');
        Schema::dropIfExists('kartu_stok');
    }
};
