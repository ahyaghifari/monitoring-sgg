<?php

namespace App\Livewire;

use App\Models\Project;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

// Tabel tugas di modal "Kelola Tugas"; setiap perubahan langsung tersimpan
class ManageProjectTasks extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public Project $project;

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->project->checklists())
            ->columns([
                // Centang selesai: tersimpan saat diklik, status project ikut tersinkron
                CheckboxColumn::make('is_completed')
                    ->label('Selesai')
                    ->width('1%'),

                // Ubah nama tugas langsung di baris, tersimpan saat fokus keluar
                TextInputColumn::make('title')
                    ->label('Nama Tugas')
                    ->rules(['required', 'max:255']),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Tugas')
                    ->modalHeading('Tambah Tugas')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal')
                    ->createAnother(false)
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama Tugas')
                            ->required()
                            ->maxLength(255),
                    ]),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Tugas')
                    ->modalDescription('Yakin ingin menghapus tugas ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->emptyStateHeading('Belum ada tugas')
            ->emptyStateDescription('Klik "Tambah Tugas" untuk menambahkan tugas pertama.');
    }

    public function render(): View
    {
        return view('livewire.manage-project-tasks');
    }
}
