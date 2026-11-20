<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuPenjualan extends Model
{
    protected $table = 'ju_penjualan';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_penjualan',
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

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }
}
