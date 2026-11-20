<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PembelianResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Pembelian';

    protected static ?string $pluralModelLabel = 'Pembelian';

    public static function allowedRoles(): array
    {
        return ['Owner', 'Admin Pembelian', 'Admin Hutang'];
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
                Tables\Columns\TextColumn::make('nota_pembelian')
                    ->label('No. Nota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
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
                Tables\Columns\TextColumn::make('sisa_hutang')
                    ->label('Sisa Hutang')
                    ->money('IDR')
                    ->state(fn (Pembelian $record): float => $record->sisa_hutang),
                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.nama_user')
                    ->label('Diinput')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_verifikasi')
                    ->label('Status')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Terverifikasi' => 'Terverifikasi',
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
            'index' => Pages\ListPembelians::route('/'),
        ];
    }
}
