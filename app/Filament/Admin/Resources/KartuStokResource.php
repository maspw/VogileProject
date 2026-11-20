<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KartuStokResource\Pages;
use App\Filament\Admin\Resources\KartuStokResource\RelationManagers;
use App\Models\KartuStok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KartuStokResource extends Resource
{
    protected static ?string $model = KartuStok::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_barang')
                    ->required()
                    ->maxLength(30),
                Forms\Components\DateTimePicker::make('tanggal')
                    ->required(),
                Forms\Components\TextInput::make('jenis_transaksi')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('no_referensi')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('masuk')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('keluar')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('saldo')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('harga_satuan')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('masuk')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keluar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_satuan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListKartuStoks::route('/'),
            'create' => Pages\CreateKartuStok::route('/create'),
            'edit' => Pages\EditKartuStok::route('/{record}/edit'),
        ];
    }
}
