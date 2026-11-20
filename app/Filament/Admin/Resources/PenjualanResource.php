<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PenjualanResource\Pages;
use App\Filament\Admin\Resources\PenjualanResource\RelationManagers;
use App\Models\Penjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
                Forms\Components\TextInput::make('id_pelanggan')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('id_user')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('metode_pembayaran')
                    ->required()
                    ->maxLength(20)
                    ->default('Tunai'),
                Forms\Components\DatePicker::make('jatuh_tempo'),
                Forms\Components\TextInput::make('status_verifikasi')
                    ->required()
                    ->maxLength(20)
                    ->default('Menunggu'),
                Forms\Components\TextInput::make('verified_by')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\DateTimePicker::make('verified_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_penjualan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_user')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
}
