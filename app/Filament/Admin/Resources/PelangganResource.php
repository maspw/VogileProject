<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\PelangganResource\Pages;
use App\Models\Pelanggan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PelangganResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Pelanggan::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Pelanggan';

    protected static ?string $pluralModelLabel = 'Pelanggan';

    protected static ?string $recordTitleAttribute = 'nama';

    public static function allowedRoles(): array
    {
        return ['Owner', 'Sales', 'Admin Penjualan', 'Admin Piutang'];
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('no_telp')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\Textarea::make('alamat')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('limit_piutang')
                    ->label('Limit Piutang')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_pelanggan')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_telp')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('limit_piutang')
                    ->label('Limit Piutang')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_piutang')
                    ->label('Piutang Berjalan')
                    ->money('IDR')
                    ->state(fn (Pelanggan $record): float => $record->total_piutang),
                Tables\Columns\TextColumn::make('sisa_limit')
                    ->label('Sisa Limit')
                    ->money('IDR')
                    ->state(fn (Pelanggan $record): float => $record->sisa_limit)
                    ->color(fn (Pelanggan $record): string => $record->sisa_limit <= 0 ? 'danger' : 'success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPelanggans::route('/'),
            'create' => Pages\CreatePelanggan::route('/create'),
            'edit' => Pages\EditPelanggan::route('/{record}/edit'),
        ];
    }
}
