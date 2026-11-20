<?php

namespace App\Filament\Admin\Concerns;

trait HasRoleAccess
{
    /**
     * @return array<int, string>
     */
    public static function allowedRoles(): array
    {
        return ['Owner'];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasAnyRole(static::allowedRoles());
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }
}
