<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\KartuStokResource\Pages;
use App\Models\KartuStok;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KartuStokResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = KartuStok::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Persediaan';

    protected static ?string $modelLabel = 'Kartu Stok';

    protected static ?string $pluralModelLabel = 'Kartu Stok';

    public static function allowedRoles(): array
    {
        return ['Owner', 'Sales', 'Admin Penjualan', 'Admin Piutang', 'Admin Pembelian', 'Admin Hutang', 'Kasir'];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->label('Jenis')
                    ->badge(),
                Tables\Columns\TextColumn::make('no_referensi')
                    ->label('Referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('masuk')
                    ->numeric()
                    ->color('success'),
                Tables\Columns\TextColumn::make('keluar')
                    ->numeric()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('saldo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('keterangan')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options(fn (): array => KartuStok::query()
                        ->distinct()
                        ->orderBy('jenis_transaksi')
                        ->pluck('jenis_transaksi', 'jenis_transaksi')
                        ->all()),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKartuStoks::route('/'),
        ];
    }
}
