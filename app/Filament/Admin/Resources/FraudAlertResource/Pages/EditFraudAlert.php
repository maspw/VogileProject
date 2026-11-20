<?php

namespace App\Filament\Admin\Resources\FraudAlertResource\Pages;

use App\Filament\Admin\Resources\FraudAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFraudAlert extends EditRecord
{
    protected static string $resource = FraudAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
