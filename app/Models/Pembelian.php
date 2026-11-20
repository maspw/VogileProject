<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembelian extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'nota_pembelian';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nota_pembelian',
        'tanggal',
        'id_supplier',
        'id_user',
        'total',
        'metode_pembayaran',
        'jatuh_tempo',
        'status_verifikasi',
        'verified_by',
        'verified_at',
        'no_po',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id_user');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'no_po', 'no_po');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'nota_pembelian', 'nota_pembelian');
    }

    public function retur(): HasMany
    {
        return $this->hasMany(ReturPembelian::class, 'nota_pembelian', 'nota_pembelian');
    }

    public function pembayaranHutang(): HasMany
    {
        return $this->hasMany(PembayaranHutang::class, 'nota_pembelian', 'nota_pembelian');
    }

    public function juPembelian(): HasMany
    {
        return $this->hasMany(JuPembelian::class, 'nota_pembelian', 'nota_pembelian');
    }

    // Accessors
    public function getSisaHutangAttribute(): float
    {
        if ($this->metode_pembayaran !== 'Kredit') {
            return 0.0;
        }

        $terbayar = (float) $this->pembayaranHutang()
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');

        $diretur = (float) $this->retur()
            ->where('status_verifikasi', 'Terverifikasi')
            ->sum('total');

        return (float) max(0, $this->total - $terbayar - $diretur);
    }

    public function getSisaHutangTersediaAttribute(): float
    {
        if ($this->metode_pembayaran !== 'Kredit') {
            return 0.0;
        }

        $terbayarDanRetur = (float) $this->pembayaranHutang()
            ->where('status_verifikasi', '!=', 'Ditolak')
            ->sum('total')
            + (float) $this->retur()
                ->where('status_verifikasi', '!=', 'Ditolak')
                ->sum('total');

        return (float) max(0, $this->total - $terbayarDanRetur);
    }

    public function getStatusLunasAttribute(): string
    {
        if ($this->metode_pembayaran === 'Tunai') {
            return 'Lunas (Tunai)';
        }

        return $this->sisa_hutang <= 0 ? 'Lunas' : 'Belum Lunas';
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
