<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\CoaResource\Pages;
use App\Models\Coa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoaResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Coa::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?string $modelLabel = 'Akun COA';

    protected static ?string $pluralModelLabel = 'Bagan Akun (COA)';

    protected static ?string $recordTitleAttribute = 'nama_akun';

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_akun')
                    ->label('Kode Akun')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('nama_akun')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('kategori')
                    ->required()
                    ->options([
                        'Aset Lancar' => 'Aset Lancar',
                        'Kewajiban Lancar' => 'Kewajiban Lancar',
                        'Ekuitas' => 'Ekuitas',
                        'Pendapatan' => 'Pendapatan',
                        'Beban Pokok' => 'Beban Pokok',
                        'Beban Operasional' => 'Beban Operasional',
                    ]),
                Forms\Components\Select::make('saldo_normal')
                    ->required()
                    ->options([
                        'Debit' => 'Debit',
                        'Kredit' => 'Kredit',
                    ])
                    ->default('Debit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_akun')
                    ->label('Kode')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_akun')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('saldo_normal')
                    ->label('Saldo Normal')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Debit' ? 'info' : 'warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'Aset Lancar' => 'Aset Lancar',
                        'Kewajiban Lancar' => 'Kewajiban Lancar',
                        'Ekuitas' => 'Ekuitas',
                        'Pendapatan' => 'Pendapatan',
                        'Beban Pokok' => 'Beban Pokok',
                        'Beban Operasional' => 'Beban Operasional',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
