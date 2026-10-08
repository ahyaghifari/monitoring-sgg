<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Company;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Project';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Daftar Project';

    protected static ?int $navigationSort = 2;

    // -------------------------------------------------------------------------
    // Form identitas project (Create & Edit modal); tugas dikelola lewat modal Kelola Tugas
    // -------------------------------------------------------------------------

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('company_id')
                ->label('Perusahaan')
                ->options(Company::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('title')
                ->label('Nama Project')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->label('Deskripsi Project')
                ->rows(2)
                ->columnSpanFull(),

            // Pilih PIC dari master data; PIC baru bisa ditambah langsung dari sini
            Select::make('pic_id')
                ->label('PIC')
                ->relationship('pic', 'nama')
                ->searchable()
                ->preload()
                // Pakai form PicResource agar aturan wajib isi tetap sama
                ->createOptionForm(fn (Schema $schema) => PicResource::form($schema)),

            TextInput::make('pemohon')
                ->label('Pemohon')
                ->placeholder('Contoh: Ahmad Subagja')
                ->maxLength(255),

            FileUpload::make('logo')
                ->label('Logo Project')
                ->image()
                ->disk('public')
                ->directory('project-logos')
                ->imageEditor()
                ->circleCropper()
                ->maxSize(2048),

            Select::make('urgency')
                ->label('Tingkat Urgensi')
                ->options([
                    'Low'    => 'Rendah',
                    'Medium' => 'Sedang',
                    'High'   => 'Tinggi',
                    'Urgent' => 'Darurat',
                ])
                ->default('Medium')
                ->required(),

            Select::make('status')
                ->label('Status Project')
                ->options([
                    'In Progress' => 'Dalam Proses',
                    'Completed'   => 'Selesai',
                ])
                ->default('In Progress')
                ->helperText('Otomatis mengikuti daftar tugas jika project memiliki tugas.')
                ->required(),

        ])->columns(2);
    }

    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('urgency')
                    ->label('Urgensi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Low'    => 'Rendah',
                        'Medium' => 'Sedang',
                        'High'   => 'Tinggi',
                        'Urgent' => 'Darurat',
                        default  => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Low'    => 'gray',
                        'Medium' => 'info',
                        'High'   => 'warning',
                        'Urgent' => 'danger',
                        default  => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'In Progress' => 'Dalam Proses',
                        'Completed'   => 'Selesai',
                        default       => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'In Progress' => 'warning',
                        'Completed'   => 'success',
                        default       => 'gray',
                    }),

                TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn (int $state): string => "{$state}%"),
            ])
            ->defaultSort('company.name')
            // Klik baris membuka modal Kelola Tugas
            ->recordAction('manageTasks')
            ->actions([
                // Modal daftar tugas; tiap aksi tersimpan langsung, cukup tombol tutup
                Action::make('manageTasks')
                    ->label('Tugas')
                    ->icon('heroicon-o-check-circle')
                    ->modalHeading(fn (Project $record): string => "Kelola Tugas — {$record->title}")
                    ->modalContent(fn (Project $record) => view('filament.manage-tasks-modal', ['record' => $record]))
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Kembali'),

                EditAction::make()
                    ->label('Ubah')
                    ->modalHeading('Ubah Project')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Project')
                    ->modalDescription('Yakin ingin menghapus project ini? Semua tugas terkait juga akan dihapus.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ]);
    }

    // -------------------------------------------------------------------------
    // Pages — hanya index; create, edit, kelola tugas pakai modal
    // -------------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
        ];
    }
}
