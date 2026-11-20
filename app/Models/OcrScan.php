<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcrScan extends Model
{
    protected $table = 'ocr_scans';

    protected $fillable = [
        'id_user',
        'jenis_dokumen',
        'file_path',
        'hasil_json',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'hasil_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
