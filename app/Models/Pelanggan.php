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

    protected $appends = [
        'total_piutang',
        'sisa_limit',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function getTotalPiutangAttribute(): float
    {
        $penjualan = $this->penjualan()
            ->where('metode_pembayaran', 'Kredit')
            ->where('status_verifikasi', 'Terverifikasi');

        $ids = (clone $penjualan)->pluck('id_penjualan');
        $total = (float) (clone $penjualan)->sum('total');
        $terbayar = (float) PelunasanPiutang::whereIn('id_penjualan', $ids)
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');
        $diretur = (float) ReturPenjualan::whereIn('id_penjualan', $ids)
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');

        return (float) max(0, $total - $terbayar - $diretur);
    }

    public function getSisaLimitAttribute(): float
    {
        return (float) max(0, $this->limit_piutang - $this->total_piutang);
    }
}
