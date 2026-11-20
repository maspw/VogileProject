<?php

namespace App\Filament\Admin\Resources\KartuStokResource\Pages;

use App\Filament\Admin\Resources\KartuStokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKartuStoks extends ListRecords
{
    protected static string $resource = KartuStokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
