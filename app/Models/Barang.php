<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'kode_barang';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'jumlah_barang',
        'harga_barang',
        'hpp',
        'satuan',
        'min_stok',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_barang' => 'integer',
            'harga_barang' => 'decimal:2',
            'hpp' => 'decimal:2',
            'min_stok' => 'integer',
        ];
    }

    // Accessors
    public function getStatusStokAttribute(): string
    {
        if ($this->jumlah_barang <= 0) {
            return 'Habis';
        }
        if ($this->jumlah_barang <= $this->min_stok) {
            return 'Stok Menipis';
        }
        return 'Aman';
    }

    public function getNilaiStokAttribute(): float
    {
        return (float) ($this->jumlah_barang * $this->hpp);
    }

    // Relationships
    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'kode_barang', 'kode_barang');
    }

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'kode_barang', 'kode_barang');
    }

    public function kartuStok(): HasMany
    {
        return $this->hasMany(KartuStok::class, 'kode_barang', 'kode_barang');
    }

    // Scopes
    public function scopeStokMenipis(Builder $query): Builder
    {
        return $query->whereColumn('jumlah_barang', '<=', 'min_stok')->where('jumlah_barang', '>', 0);
    }

    public function scopeStokHabis(Builder $query): Builder
    {
        return $query->where('jumlah_barang', '<=', 0);
    }
}
