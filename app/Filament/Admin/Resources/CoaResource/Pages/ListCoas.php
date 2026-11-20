<?php

namespace App\Filament\Admin\Resources\CoaResource\Pages;

use App\Filament\Admin\Resources\CoaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoas extends ListRecords
{
    protected static string $resource = CoaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
