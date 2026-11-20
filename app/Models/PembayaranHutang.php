<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembayaranHutang extends Model
{
    protected $table = 'pembayaran_hutang';
    protected $primaryKey = 'no_pembayaran';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_pembayaran',
        'nota_pembelian',
        'tanggal',
        'total',
        'id_user',
        'status_verifikasi',
        'verified_by',
        'verified_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'nota_pembelian', 'nota_pembelian');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id_user');
    }

    public function juHutang(): HasMany
    {
        return $this->hasMany(JuHutang::class, 'no_pembayaran', 'no_pembayaran');
    }

    public function scopeTerverifikasi(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'Terverifikasi');
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'Menunggu');
    }

    public function scopePeriode(Builder $query, string $mulai, string $selesai): Builder
    {
        return $query->whereBetween('tanggal', [$mulai, $selesai]);
    }
}
