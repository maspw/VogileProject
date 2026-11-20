<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_penjualan',
        'tanggal',
        'id_pelanggan',
        'id_user',
        'total',
        'metode_pembayaran',
        'jatuh_tempo',
        'status_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jatuh_tempo' => 'date',
            'verified_at' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id_user');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function retur(): HasMany
    {
        return $this->hasMany(ReturPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function pelunasanPiutang(): HasMany
    {
        return $this->hasMany(PelunasanPiutang::class, 'id_penjualan', 'id_penjualan');
    }

    public function juPenjualan(): HasMany
    {
        return $this->hasMany(JuPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    // Accessors
    public function getSisaPiutangAttribute(): float
    {
        if ($this->metode_pembayaran !== 'Kredit') {
            return 0.0;
        }

        $terbayar = (float) $this->pelunasanPiutang()
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');

        $diretur = (float) $this->retur()
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');

        return (float) max(0, $this->total - $terbayar - $diretur);
    }

    public function getStatusLunasAttribute(): string
    {
        if ($this->metode_pembayaran === 'Tunai') {
            return 'Lunas (Tunai)';
        }

        return $this->sisa_piutang <= 0 ? 'Lunas' : 'Belum Lunas';
    }

    // Scopes
    public function scopeTerverifikasi(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'Terverifikasi');
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'Menunggu');
    }

    public function scopeKredit(Builder $query): Builder
    {
        return $query->where('metode_pembayaran', 'Kredit');
    }

    public function scopePeriode(Builder $query, string $mulai, string $selesai): Builder
    {
        return $query->whereBetween('tanggal', [$mulai, $selesai]);
    }

    public function scopeJatuhTempo(Builder $query): Builder
    {
        return $query->where('metode_pembayaran', 'Kredit')
            ->whereNotNull('jatuh_tempo')
            ->where('jatuh_tempo', '<=', now()->toDateString());
    }
}
