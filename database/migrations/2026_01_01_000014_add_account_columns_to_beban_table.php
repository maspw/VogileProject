<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beban', function (Blueprint $table) {
            $table->integer('kode_akun')->nullable()->after('total');
            $table->string('metode_bayar', 20)->nullable()->after('kode_akun');
        });

        DB::table('beban')->select('id_beban', 'catatan')->orderBy('id_beban')->each(function ($beban) {
            $catatan = $beban->catatan ?? '';

            if (preg_match('/\[Akun:\s*(\d+)\]/', $catatan, $matches)) {
                $kodeAkun = (int) $matches[1];

                if (DB::table('coa')->where('kode_akun', $kodeAkun)->exists()) {
                    DB::table('beban')->where('id_beban', $beban->id_beban)->update(['kode_akun' => $kodeAkun]);
                }
            }

            if (preg_match('/\[Bayar via:\s*([^\]]+)\]/', $catatan, $matches)) {
                $metode = trim($matches[1]);

                if (in_array($metode, ['Kas', 'Bank BCA'], true)) {
                    DB::table('beban')->where('id_beban', $beban->id_beban)->update(['metode_bayar' => $metode]);
                }
            }
        });

        Schema::table('beban', function (Blueprint $table) {
            $table->foreign('kode_akun')->references('kode_akun')->on('coa')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('beban', function (Blueprint $table) {
            $table->dropForeign(['kode_akun']);
            $table->dropColumn(['kode_akun', 'metode_bayar']);
        });
    }
};
