<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailReturPenjualan extends Model
{
    protected $table = 'detail_retur_penjualan';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_retur_penjualan',
        'kode_barang',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function returPenjualan(): BelongsTo
    {
        return $this->belongsTo(ReturPenjualan::class, 'no_retur_penjualan', 'no_retur_penjualan');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }
}
