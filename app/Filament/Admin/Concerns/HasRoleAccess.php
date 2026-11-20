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

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(static::allowedRoles());
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function canEdit($record): bool
    {
        return static::canAccess();
    }

    public static function canDelete($record): bool
    {
        return static::canAccess();
    }
}
