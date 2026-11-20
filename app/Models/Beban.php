<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beban extends Model
{
    protected $table = 'beban';
    protected $primaryKey = 'id_beban';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_beban',
        'nama_beban',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id_user');
    }

    public function juBeban(): HasMany
    {
        return $this->hasMany(JuBeban::class, 'id_beban', 'id_beban');
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
