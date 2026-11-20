<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PelunasanPiutang extends Model
{
    protected $table = 'pelunasan_piutang';
    protected $primaryKey = 'no_pelunasan';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_pelunasan',
        'id_penjualan',
        'tanggal',
        'total',
        'metode_bayar',
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

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id_user');
    }

    public function juPiutang(): HasMany
    {
        return $this->hasMany(JuPiutang::class, 'no_pelunasan', 'no_pelunasan');
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
