<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FraudAlert extends Model
{
    protected $table = 'fraud_alerts';

    protected $fillable = [
        'kode_aturan',
        'kategori',
        'tingkat_risiko',
        'skor_risiko',
        'no_referensi',
        'tabel_referensi',
        'deskripsi',
        'bukti_json',
        'status',
        'id_user_pelaku',
        'id_user_reviewer',
        'catatan_review',
    ];

    protected function casts(): array
    {
        return [
            'skor_risiko' => 'integer',
            'bukti_json' => 'array',
        ];
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_pelaku', 'id_user');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_reviewer', 'id_user');
    }

    public function scopeMenungguReview(Builder $query): Builder
    {
        return $query->where('status', 'Menunggu Review');
    }

    public function scopeTinggiKritis(Builder $query): Builder
    {
        return $query->whereIn('tingkat_risiko', ['Tinggi', 'Kritis']);
    }
}
