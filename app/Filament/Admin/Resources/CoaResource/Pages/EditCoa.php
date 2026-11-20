<?php

namespace App\Filament\Admin\Resources\CoaResource\Pages;

use App\Filament\Admin\Resources\CoaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoa extends EditRecord
{
    protected static string $resource = CoaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
