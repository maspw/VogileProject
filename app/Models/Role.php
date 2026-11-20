<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id_role';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_role',
        'nama_role',
        'no_telp',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }
}
