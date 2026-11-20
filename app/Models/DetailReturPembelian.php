<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailReturPembelian extends Model
{
    protected $table = 'detail_retur_pembelian';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_retur',
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

    public function returPembelian(): BelongsTo
    {
        return $this->belongsTo(ReturPembelian::class, 'no_retur', 'no_retur');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }
}
