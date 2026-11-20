<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    /**
     * Generate nomor dokumen transaksi: PREFIKS-YYYYMM-XXX
     * Contoh: PJ-202611-001, PB-202611-001, PO-202611-001
     */
    public static function generate(string $prefix, string $table, string $column, ?string $date = null, int $padding = 3): string
    {
        $yearMonth = Carbon::parse($date ?? now())->format('Ym');
        $searchPrefix = "{$prefix}-{$yearMonth}-";

        $lastRecord = DB::table($table)
            ->where($column, 'LIKE', "{$searchPrefix}%")
            ->orderBy($column, 'desc')
            ->lockForUpdate()
            ->value($column);

        $nextSequence = $lastRecord
            ? (int) substr($lastRecord, strlen($searchPrefix)) + 1
            : 1;

        return sprintf("%s%0{$padding}d", $searchPrefix, $nextSequence);
    }

    /**
     * Generate kode master sederhana: PREFIKS + nomor urut.
     * Contoh: BRG011, PLG009, SUP006
     */
    public static function master(string $prefix, string $table, string $column, int $padding = 3): string
    {
        $codes = DB::table($table)
            ->where($column, 'LIKE', "{$prefix}%")
            ->lockForUpdate()
            ->pluck($column);

        $maxSequence = 0;

        foreach ($codes as $code) {
            $suffix = substr((string) $code, strlen($prefix));

            if ($suffix !== '' && ctype_digit($suffix)) {
                $maxSequence = max($maxSequence, (int) $suffix);
            }
        }

        return sprintf("%s%0{$padding}d", $prefix, $maxSequence + 1);
    }

    /**
     * Generate kode akun COA berikutnya (integer).
     */
    public static function nextInteger(string $table, string $column): int
    {
        return ((int) DB::table($table)->lockForUpdate()->max($column)) + 1;
    }
}
