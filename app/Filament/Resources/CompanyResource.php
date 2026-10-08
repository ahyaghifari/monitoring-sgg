<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Perusahaan';

    protected static ?string $modelLabel = 'Perusahaan';

    protected static ?string $pluralModelLabel = 'Daftar Perusahaan';

    protected static ?int $navigationSort = 1;

    // Hanya Super Admin; Admin/Staff tidak melihat menu ini
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    // -------------------------------------------------------------------------
    // Form
    // -------------------------------------------------------------------------

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Perusahaan')
                ->required()
                ->maxLength(255),
        ]);
    }

    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('projects_count')
                    ->label('Jumlah Project')
                    ->counts('projects')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->recordUrl(null)           // Klik baris tidak membuka apa-apa
            ->actions([
                EditAction::make()
                    ->label('Ubah')
                    ->modalHeading('Ubah Perusahaan')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Perusahaan')
                    ->modalDescription('Yakin ingin menghapus perusahaan ini? Semua project terkait juga akan dihapus.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ]);
    }

    // -------------------------------------------------------------------------
    // Pages — hanya index; create & edit pakai modal
    // -------------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
        ];
    }
}
