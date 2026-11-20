<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelunasan_piutang', function (Blueprint $table) {
            $table->string('metode_bayar', 20)->nullable()->after('total');
        });

        Schema::table('pembayaran_hutang', function (Blueprint $table) {
            $table->string('metode_bayar', 20)->nullable()->after('total');
        });

        $targets = [
            'pelunasan_piutang' => 'no_pelunasan',
            'pembayaran_hutang' => 'no_pembayaran',
        ];

        foreach ($targets as $table => $key) {
            $rows = DB::table($table)->whereNotNull('catatan')->get();

            foreach ($rows as $row) {
                if (preg_match('/\[(?:Penerimaan via|Metode):\s*(Kas|Bank BCA)\]/', (string) $row->catatan, $m)) {
                    $catatan = trim(preg_replace('/\s*\[(?:Penerimaan via|Metode):\s*(?:Kas|Bank BCA)\]/', '', (string) $row->catatan));

                    DB::table($table)->where($key, $row->{$key})->update([
                        'metode_bayar' => $m[1],
                        'catatan' => $catatan !== '' ? $catatan : null,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('pelunasan_piutang', function (Blueprint $table) {
            $table->dropColumn('metode_bayar');
        });

        Schema::table('pembayaran_hutang', function (Blueprint $table) {
            $table->dropColumn('metode_bayar');
        });
    }
};
