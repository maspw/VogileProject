<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuStok extends Model
{
    protected $table = 'kartu_stok';

    protected $fillable = [
        'kode_barang',
        'tanggal',
        'jenis_transaksi',
        'no_referensi',
        'masuk',
        'keluar',
        'saldo',
        'harga_satuan',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'masuk' => 'integer',
            'keluar' => 'integer',
            'saldo' => 'integer',
            'harga_satuan' => 'decimal:2',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    public function scopeBarang(Builder $query, string $kodeBarang): Builder
    {
        return $query->where('kode_barang', $kodeBarang);
    }

    public function scopePeriode(Builder $query, string $mulai, string $selesai): Builder
    {
        return $query->whereBetween('tanggal', [$mulai, $selesai]);
    }
}
