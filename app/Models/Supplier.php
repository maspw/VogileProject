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

    protected $appends = [
        'total_hutang',
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
        $pembelian = $this->pembelian()
            ->where('metode_pembayaran', 'Kredit')
            ->where('status_verifikasi', 'Terverifikasi');

        $ids = (clone $pembelian)->pluck('nota_pembelian');
        $total = (float) (clone $pembelian)->sum('total');
        $terbayar = (float) PembayaranHutang::whereIn('nota_pembelian', $ids)
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');
        $diretur = (float) ReturPembelian::whereIn('nota_pembelian', $ids)
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');

        return (float) max(0, $total - $terbayar - $diretur);
    }
}
