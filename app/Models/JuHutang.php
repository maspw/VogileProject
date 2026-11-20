<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuHutang extends Model
{
    protected $table = 'ju_hutang';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_pembayaran',
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

    public function pembayaranHutang(): BelongsTo
    {
        return $this->belongsTo(PembayaranHutang::class, 'no_pembayaran', 'no_pembayaran');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }
}
