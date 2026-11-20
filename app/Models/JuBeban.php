<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuBeban extends Model
{
    protected $table = 'ju_beban';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_beban',
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

    public function beban(): BelongsTo
    {
        return $this->belongsTo(Beban::class, 'id_beban', 'id_beban');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }
}
