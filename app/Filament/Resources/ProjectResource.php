<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Company;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
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

    private const URGENCY_LABELS = [
        'Low' => 'Rendah',
        'Medium' => 'Sedang',
        'High' => 'Tinggi',
        'Urgent' => 'Darurat',
    ];

    private const STATUS_LABELS = [
        'In Progress' => 'Dalam Proses',
        'Completed' => 'Selesai',
    ];

    private const URGENCY_COLORS = [
        'Low' => 'gray',
        'Medium' => 'info',
        'High' => 'warning',
        'Urgent' => 'danger',
    ];

    private const STATUS_COLORS = [
        'In Progress' => 'warning',
        'Completed' => 'success',
    ];

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

            Select::make('pic_kedua_id')
                ->label('PIC Kedua')
                ->helperText('Opsional. Tampil di belakang PIC utama pada kartu.')
                ->relationship('picKedua', 'nama')
                ->different('pic_id')
                ->searchable()
                ->preload(),

            TextInput::make('pemohon')
                ->label('Pemohon')
                ->placeholder('Contoh: Ahmad Subagja')
                ->maxLength(255),

            Textarea::make('catatan_pic')
                ->label('Catatan PIC')
                ->rows(2)
                ->columnSpanFull(),

            FileUpload::make('logo')
                ->label('Logo Project')
                ->image()
                ->disk('public')
                ->directory('project-logos')
                ->imageEditor()
                ->circleCropper()
                ->maxSize(2048),

            ColorPicker::make('warna')
                ->label('Warna Project')
                ->helperText('Dipakai untuk strip, bayangan, dan progress kartu di dashboard. Kosongkan untuk warna bawaan.')
                ->regex('/^#[0-9a-fA-F]{6}$/'),

            Select::make('urgency')
                ->label('Tingkat Urgensi')
                ->options(self::URGENCY_LABELS)
                ->default('Medium')
                ->required(),

            Select::make('status')
                ->label('Status Project')
                ->options(self::STATUS_LABELS)
                ->default('In Progress')
                ->helperText('Otomatis mengikuti progress jika project memiliki tugas.')
                ->required(),

            Radio::make('progress_mode')
                ->label('Mode Progress')
                ->options([
                    'auto' => 'Otomatis (Checklist)',
                    'manual' => 'Manual',
                ])
                ->default('auto')
                ->inline()
                ->live()
                ->required()
                ->columnSpanFull(),

            self::checklistRepeater(),
            self::manualProgressInput(),

        ])->columns(2);
    }

    // Form & modal Kelola Tugas hanya menampilkan isian mode aktif.
    // Field yang tersembunyi tidak ikut disimpan, jadi data mode lain (task / angka manual) tetap utuh.

    // Checklist task: hanya tampil di mode auto, tersimpan saat Simpan
    protected static function checklistRepeater(): Repeater
    {
        return Repeater::make('checklists')
            ->label('Checklist Task')
            ->relationship()
            ->schema([
                TextInput::make('title')
                    ->label('Nama Tugas')
                    ->required()
                    ->maxLength(255),

                Checkbox::make('is_completed')
                    ->label('Selesai')
                    ->inline(false),
            ])
            ->columns(2)
            ->addActionLabel('Tambah Tugas')
            ->defaultItems(0)
            ->reorderable(false)
            ->visible(fn (Get $get): bool => $get('progress_mode') !== 'manual')
            ->columnSpanFull();
    }

    // Persentase progress manual: satu angka 0-100, hanya tampil di mode manual
    protected static function manualProgressInput(): TextInput
    {
        $rangeMessage = 'Nilai persentase harus berada di antara 0 hingga 100. Silakan sesuaikan kembali nilai isian Anda.';

        return TextInput::make('manual_progress')
            ->label('Persentase Progress (%)')
            ->integer()
            ->minValue(0)
            ->maxValue(100)
            ->step(1)
            ->default(0)
            ->suffix('%')
            ->required()
            ->validationMessages([
                'min' => $rangeMessage,
                'max' => $rangeMessage,
            ])
            ->visible(fn (Get $get): bool => $get('progress_mode') === 'manual');
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
                    ->formatStateUsing(fn (string $state): string => self::URGENCY_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => self::URGENCY_COLORS[$state] ?? 'gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray'),

                TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn (int $state): string => "{$state}%"),
            ])
            ->defaultSort('company.name')
            // Klik baris membuka modal Kelola Tugas
            ->recordAction('manageTasks')
            ->actions([
                // Pemilih mode di atas; mode, checklist, dan persentase manual tersimpan bersamaan saat Simpan
                Action::make('manageTasks')
                    ->label('Tugas')
                    ->icon('heroicon-o-check-circle')
                    ->modalHeading(fn (Project $record): string => "Kelola Tugas — {$record->title}")
                    ->modalDescription('Perubahan mode, checklist, dan persentase manual tersimpan setelah tombol Simpan diklik.')
                    ->fillForm(fn (Project $record): array => $record->only(['progress_mode', 'manual_progress']))
                    ->schema([
                        ToggleButtons::make('progress_mode')
                            ->label('Mode Progress')
                            ->options([
                                'auto' => 'Otomatis (Checklist)',
                                'manual' => 'Manual (Persentase)',
                            ])
                            ->icons([
                                'auto' => 'heroicon-o-check-circle',
                                'manual' => 'heroicon-o-chart-bar',
                            ])
                            ->inline()
                            ->live()
                            ->required(),

                        self::checklistRepeater(),
                        self::manualProgressInput(),
                    ])
                    ->action(function (array $data, Project $record, Action $action): void {
                        $record->update($data);
                        $action->success();
                    })
                    ->successNotificationTitle('Perubahan berhasil disimpan.')
                    ->modalWidth('3xl')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Tutup'),

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
