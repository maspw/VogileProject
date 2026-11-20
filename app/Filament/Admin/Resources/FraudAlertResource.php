<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FraudAlertResource\Pages;
use App\Filament\Admin\Resources\FraudAlertResource\RelationManagers;
use App\Models\FraudAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FraudAlertResource extends Resource
{
    protected static ?string $model = FraudAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_aturan')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('kategori')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('tingkat_risiko')
                    ->required()
                    ->maxLength(20)
                    ->default('Sedang'),
                Forms\Components\TextInput::make('skor_risiko')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('no_referensi')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('tabel_referensi')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('bukti_json')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(30)
                    ->default('Menunggu Review'),
                Forms\Components\TextInput::make('id_user_pelaku')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\TextInput::make('id_user_reviewer')
                    ->maxLength(20)
                    ->default(null),
                Forms\Components\Textarea::make('catatan_review')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_aturan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tingkat_risiko')
                    ->searchable(),
                Tables\Columns\TextColumn::make('skor_risiko')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tabel_referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_user_pelaku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_user_reviewer')
                    ->searchable(),
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
            'index' => Pages\ListFraudAlerts::route('/'),
            'create' => Pages\CreateFraudAlert::route('/create'),
            'edit' => Pages\EditFraudAlert::route('/{record}/edit'),
        ];
    }
}
