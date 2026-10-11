<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\PicResource\Pages;
use App\Models\Pic;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
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

            FileUpload::make('foto')
                ->label('Foto')
                ->image()
                ->avatar()
                ->disk('public')
                ->directory('pic-photos')
                ->imageEditor()
                ->circleCropper()
                ->maxSize(2048),

            // Akun agar PIC bisa login di beranda dan mengelola tugas project miliknya
            Select::make('user_id')
                ->label('Akun Login PIC')
                ->helperText('Opsional. PIC dengan akun bisa menambah, mencentang, dan menghapus tugas di beranda.')
                ->options(fn (): array => User::query()->where('role', UserRole::PIC->value)->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->unique(table: 'pics', column: 'user_id', ignoreRecord: true)
                ->createOptionForm([
                    TextInput::make('name')->label('Nama')->required()->maxLength(255),
                    TextInput::make('email')->label('Email')->email()->required()->maxLength(255)->unique(table: 'users', column: 'email'),
                    TextInput::make('password')->label('Password')->password()->revealable()->required()->minLength(8)->maxLength(255),
                ])
                ->createOptionUsing(fn (array $data): int => User::create([...$data, 'role' => UserRole::PIC])->getKey()),

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
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Akun Login')
                    ->placeholder('Belum ada akun'),

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
