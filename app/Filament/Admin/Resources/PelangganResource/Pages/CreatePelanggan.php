<?php

namespace App\Filament\Admin\Resources\PelangganResource\Pages;

use App\Filament\Admin\Resources\PelangganResource;
use App\Services\DocumentNumberGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreatePelanggan extends CreateRecord
{
    protected static string $resource = PelangganResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_pelanggan'] = DocumentNumberGenerator::master('PLG', 'pelanggan', 'id_pelanggan');

        return $data;
    }
}
