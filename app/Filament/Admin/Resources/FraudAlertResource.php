<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\FraudAlertResource\Pages;
use App\Models\FraudAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FraudAlertResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = FraudAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'Pengawasan';

    protected static ?string $modelLabel = 'Fraud Alert';

    protected static ?string $pluralModelLabel = 'Fraud Alert';

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
                Forms\Components\Select::make('tingkat_risiko')
                    ->required()
                    ->options([
                        'Rendah' => 'Rendah',
                        'Sedang' => 'Sedang',
                        'Tinggi' => 'Tinggi',
                        'Kritis' => 'Kritis',
                    ])
                    ->default('Sedang'),
                Forms\Components\TextInput::make('skor_risiko')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                Forms\Components\TextInput::make('no_referensi')
                    ->maxLength(50),
                Forms\Components\TextInput::make('tabel_referensi')
                    ->maxLength(50),
                Forms\Components\Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('bukti_json')
                    ->label('Bukti (JSON)')
                    ->rule('json')
                    ->rows(5)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'Menunggu Review' => 'Menunggu Review',
                        'Dalam Investigasi' => 'Dalam Investigasi',
                        'Selesai' => 'Selesai',
                        'Abaikan' => 'Abaikan',
                    ])
                    ->default('Menunggu Review'),
                Forms\Components\TextInput::make('id_user_pelaku')
                    ->label('ID User Pelaku')
                    ->maxLength(20),
                Forms\Components\TextInput::make('id_user_reviewer')
                    ->label('ID User Reviewer')
                    ->maxLength(20),
                Forms\Components\Textarea::make('catatan_review')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_aturan')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tingkat_risiko')
                    ->label('Risiko')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Kritis' => 'danger',
                        'Tinggi' => 'warning',
                        'Sedang' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('skor_risiko')
                    ->label('Skor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_referensi')
                    ->label('Referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('pelaku.nama_user')
                    ->label('Pelaku')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tingkat_risiko')
                    ->label('Tingkat Risiko')
                    ->options([
                        'Rendah' => 'Rendah',
                        'Sedang' => 'Sedang',
                        'Tinggi' => 'Tinggi',
                        'Kritis' => 'Kritis',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Menunggu Review' => 'Menunggu Review',
                        'Dalam Investigasi' => 'Dalam Investigasi',
                        'Selesai' => 'Selesai',
                        'Abaikan' => 'Abaikan',
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
            'index' => Pages\ListFraudAlerts::route('/'),
            'create' => Pages\CreateFraudAlert::route('/create'),
            'edit' => Pages\EditFraudAlert::route('/{record}/edit'),
        ];
    }
}
