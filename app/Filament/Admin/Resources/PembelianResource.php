<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PembelianResource\Pages;
use App\Filament\Admin\Resources\PembelianResource\RelationManagers;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
                Forms\Components\TextInput::make('id_supplier')
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
                Forms\Components\TextInput::make('no_po')
                    ->maxLength(30)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nota_pembelian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_supplier')
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
                Tables\Columns\TextColumn::make('no_po')
                    ->searchable(),
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
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}
