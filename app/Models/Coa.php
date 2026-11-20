<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coa extends Model
{
    protected $table = 'coa';
    protected $primaryKey = 'kode_akun';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kategori',
        'saldo_normal',
    ];

    protected function casts(): array
    {
        return [
            'kode_akun' => 'integer',
        ];
    }

    public function juPenjualan(): HasMany
    {
        return $this->hasMany(JuPenjualan::class, 'kode_akun', 'kode_akun');
    }

    public function juPembelian(): HasMany
    {
        return $this->hasMany(JuPembelian::class, 'kode_akun', 'kode_akun');
    }

    public function juBeban(): HasMany
    {
        return $this->hasMany(JuBeban::class, 'kode_akun', 'kode_akun');
    }
}
