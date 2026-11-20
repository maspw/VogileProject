<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuPiutang extends Model
{
    protected $table = 'ju_piutang';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_pelunasan',
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

    public function pelunasanPiutang(): BelongsTo
    {
        return $this->belongsTo(PelunasanPiutang::class, 'no_pelunasan', 'no_pelunasan');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }
}
