<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    /**
     * Generate format nomor dokumen standar: PREFIKS-YYYYMM-XXXX
     * Contoh: PJ-202611-0001, PB-202611-0001, PO-202611-0001
     */
    public static function generate(string $prefix, string $table, string $column): string
    {
        $yearMonth = date('Ym');
        $searchPrefix = "{$prefix}-{$yearMonth}-";

        $lastRecord = DB::table($table)
            ->where($column, 'LIKE', "{$searchPrefix}%")
            ->orderBy($column, 'desc')
            ->value($column);

        if (!$lastRecord) {
            $nextSequence = 1;
        } else {
            $lastSequence = (int) substr($lastRecord, strlen($searchPrefix));
            $nextSequence = $lastSequence + 1;
        }

        return sprintf('%s%04d', $searchPrefix, $nextSequence);
    }
}
