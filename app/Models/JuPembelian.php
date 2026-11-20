<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuPembelian extends Model
{
    protected $table = 'ju_pembelian';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nota_pembelian',
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

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'nota_pembelian', 'nota_pembelian');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }
}
