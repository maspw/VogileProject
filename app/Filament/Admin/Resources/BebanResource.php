<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BebanResource\Pages;
use App\Filament\Admin\Resources\BebanResource\RelationManagers;
use App\Models\Beban;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BebanResource extends Resource
{
    protected static ?string $model = Beban::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_beban')
                    ->required()
                    ->maxLength(100),
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('id_user')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('status_verifikasi')
                    ->required()
                    ->maxLength(20)
                    ->default('Menunggu'),
                Forms\Components\TextInput::make('verified_by')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\DateTimePicker::make('verified_at'),
                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_beban')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_beban')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_user')
                    ->searchable(),
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
            'index' => Pages\ListBebans::route('/'),
            'create' => Pages\CreateBeban::route('/create'),
            'edit' => Pages\EditBeban::route('/{record}/edit'),
        ];
    }
}
