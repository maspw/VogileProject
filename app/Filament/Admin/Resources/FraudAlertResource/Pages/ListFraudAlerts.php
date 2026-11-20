<?php

namespace App\Filament\Admin\Resources\FraudAlertResource\Pages;

use App\Filament\Admin\Resources\FraudAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFraudAlerts extends ListRecords
{
    protected static string $resource = FraudAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
