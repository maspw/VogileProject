<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\PenjualanResource\Pages;
use App\Models\Penjualan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PenjualanResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Penjualan';

    protected static ?string $pluralModelLabel = 'Penjualan';

    public static function allowedRoles(): array
    {
        return ['Owner', 'Sales', 'Admin Penjualan', 'Kasir'];
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
                Tables\Columns\TextColumn::make('id_penjualan')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Tunai' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Terverifikasi' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('sisa_piutang')
                    ->label('Sisa Piutang')
                    ->money('IDR')
                    ->state(fn (Penjualan $record): float => $record->sisa_piutang),
                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.nama_user')
                    ->label('Kasir')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_verifikasi')
                    ->label('Status')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Terverifikasi' => 'Terverifikasi',
                        'Dibatalkan' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode')
                    ->options([
                        'Tunai' => 'Tunai',
                        'Kredit' => 'Kredit',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
        ];
    }
}
