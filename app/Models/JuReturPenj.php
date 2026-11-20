<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuReturPenj extends Model
{
    protected $table = 'ju_retur_penj';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_retur_penjualan',
        'kode_akun',
        'debit',
        'kredit',
        'keterangan',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'kode_akun' => 'integer',
            'debit' => 'integer',
            'kredit' => 'integer',
            'tanggal' => 'date',
        ];
    }

    public function returPenjualan(): BelongsTo
    {
        return $this->belongsTo(ReturPenjualan::class, 'no_retur_penjualan', 'no_retur_penjualan');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }
}
