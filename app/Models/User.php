<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_user',
        'nama_user',
        'username',
        'id_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role !== null;
    }

    // Role Helper Methods
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        if (is_array($roles)) {
            return $this->hasAnyRole($roles);
        }

        return strtolower($this->role->nama_role) === strtolower($roles);
    }

    public function hasAnyRole(array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        return in_array($this->role->nama_role, $roles, true);
    }

    public function isOwner(): bool
    {
        return $this->hasRole('Owner');
    }

    public function isKasir(): bool
    {
        return $this->hasRole('Kasir');
    }

    public function isSales(): bool
    {
        return $this->hasRole('Sales');
    }

    public function isAdminPenjualan(): bool
    {
        return $this->hasRole('Admin Penjualan');
    }

    public function isAdminPiutang(): bool
    {
        return $this->hasRole('Admin Piutang');
    }

    public function isAdminPembelian(): bool
    {
        return $this->hasRole('Admin Pembelian');
    }

    public function isAdminHutang(): bool
    {
        return $this->hasRole('Admin Hutang');
    }

    public function scopeRole(Builder $query, string $roleName): Builder
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('nama_role', $roleName);
        });
    }
}
