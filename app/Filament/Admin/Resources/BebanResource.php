<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\BebanResource\Pages;
use App\Models\Beban;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BebanResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Beban::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?string $modelLabel = 'Beban Operasional';

    protected static ?string $pluralModelLabel = 'Beban Operasional';

    public static function allowedRoles(): array
    {
        return ['Owner', 'Kasir'];
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
                Tables\Columns\TextColumn::make('id_beban')
                    ->label('No. Beban')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_beban')
                    ->label('Nama Beban')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('coa.nama_akun')
                    ->label('Akun')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('metode_bayar')
                    ->label('Sumber Dana')
                    ->badge(),
                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Terverifikasi' ? 'success' : 'warning'),
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
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBebans::route('/'),
        ];
    }
}
