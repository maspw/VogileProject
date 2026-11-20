<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'nama',
        'alamat',
        'no_telp',
        'limit_piutang',
    ];

    protected function casts(): array
    {
        return [
            'limit_piutang' => 'decimal:2',
        ];
    }

    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function getTotalPiutangAttribute(): float
    {
        return (float) $this->penjualan()
            ->where('metode_pembayaran', 'Kredit')
            ->where('status_verifikasi', 'Terverifikasi')
            ->get()
            ->sum('sisa_piutang');
    }

    public function getSisaLimitAttribute(): float
    {
        return (float) max(0, $this->limit_piutang - $this->total_piutang);
    }
}
