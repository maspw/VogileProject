<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CoaResource\Pages;
use App\Filament\Admin\Resources\CoaResource\RelationManagers;
use App\Models\Coa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoaResource extends Resource
{
    protected static ?string $model = Coa::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_akun')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('kategori')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('saldo_normal')
                    ->required()
                    ->maxLength(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_akun')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_akun')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('saldo_normal')
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
            'index' => Pages\ListCoas::route('/'),
            'create' => Pages\CreateCoa::route('/create'),
            'edit' => Pages\EditCoa::route('/{record}/edit'),
        ];
    }
}
