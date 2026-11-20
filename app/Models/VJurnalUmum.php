<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VJurnalUmum extends Model
{
    protected $table = 'v_jurnal_umum';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'tanggal' => 'date',
        'kode_akun' => 'integer',
        'debit' => 'integer',
        'kredit' => 'integer',
    ];

    public function scopePeriode(Builder $query, string $mulai, string $selesai): Builder
    {
        return $query->whereBetween('tanggal', [$mulai, $selesai]);
    }

    public function scopeAkun(Builder $query, int $kodeAkun): Builder
    {
        return $query->where('kode_akun', $kodeAkun);
    }
}
