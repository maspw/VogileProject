<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'id_supplier';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_supplier',
        'nama_supplier',
        'alamat',
        'no_telp',
        'email',
    ];

    public function pembelian(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'id_supplier', 'id_supplier');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'id_supplier', 'id_supplier');
    }

    public function getTotalHutangAttribute(): float
    {
        return (float) $this->pembelian()
            ->where('metode_pembayaran', 'Kredit')
            ->where('status_verifikasi', 'Terverifikasi')
            ->get()
            ->sum('sisa_hutang');
    }
}
