<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HasRoleAccess;
use App\Filament\Admin\Resources\BarangResource\Pages;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BarangResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Barang';

    protected static ?string $pluralModelLabel = 'Barang';

    protected static ?string $recordTitleAttribute = 'nama_barang';

    public static function allowedRoles(): array
    {
        return ['Owner', 'Admin Pembelian', 'Admin Penjualan'];
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_barang')
                    ->required()
                    ->maxLength(150),
                Forms\Components\TextInput::make('satuan')
                    ->required()
                    ->maxLength(20)
                    ->default('Pcs'),
                Forms\Components\TextInput::make('harga_barang')
                    ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('hpp')
                    ->label('HPP (rata-rata)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->helperText('Diperbarui otomatis oleh sistem saat pembelian diverifikasi.'),
                Forms\Components\TextInput::make('min_stok')
                    ->label('Stok Minimum')
                    ->required()
                    ->numeric()
                    ->default(10)
                    ->helperText('Jumlah stok dikelola melalui transaksi pembelian/penjualan agar kartu stok tetap konsisten.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_barang')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (Barang $record): string => match ($record->status_stok) {
                        'Habis' => 'danger',
                        'Stok Menipis' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('harga_barang')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hpp')
                    ->label('HPP')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('satuan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_stok')
                    ->label('Min.')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menipis' => 'Stok Menipis',
                        'habis' => 'Stok Habis',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'menipis' => $query->stokMenipis(),
                            'habis' => $query->stokHabis(),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
