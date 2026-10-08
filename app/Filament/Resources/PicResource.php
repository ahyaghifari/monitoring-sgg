<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PicResource\Pages;
use App\Models\Pic;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PicResource extends Resource
{
    protected static ?string $model = Pic::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'PIC';

    protected static ?string $modelLabel = 'PIC';

    protected static ?string $pluralModelLabel = 'Daftar PIC';

    protected static ?int $navigationSort = 3;

    // Hanya Super Admin; Admin/Staff tidak melihat menu ini
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    // Form
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            TextInput::make('jabatan')
                ->label('Jabatan')
                ->required()
                ->maxLength(255),

            TextInput::make('divisi')
                ->label('Divisi')
                ->required()
                ->maxLength(255),
        ]);
    }

    // Table
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),

                TextColumn::make('divisi')
                    ->label('Divisi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('projects_count')
                    ->label('Jumlah Project')
                    ->counts('projects')
                    ->sortable(),
            ])
            ->defaultSort('nama')
            ->recordUrl(null)           // Klik baris tidak membuka apa-apa
            ->actions([
                EditAction::make()
                    ->label('Ubah')
                    ->modalHeading('Ubah PIC')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus PIC')
                    ->modalDescription('Yakin ingin menghapus PIC ini? Project terkait tidak ikut terhapus.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ]);
    }

    // Pages — hanya index; create & edit pakai modal
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPics::route('/'),
        ];
    }
}
