<?php

namespace App\Filament\Admin\Resources\BarangResource\Pages;

use App\Filament\Admin\Resources\BarangResource;
use App\Services\DocumentNumberGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateBarang extends CreateRecord
{
    protected static string $resource = BarangResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kode_barang'] = DocumentNumberGenerator::master('BRG', 'barang', 'kode_barang');
        $data['jumlah_barang'] = 0;

        return $data;
    }
}
