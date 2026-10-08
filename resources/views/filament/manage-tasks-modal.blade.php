{{-- Isi modal "Kelola Tugas" pada tabel project --}}
@livewire(\App\Livewire\ManageProjectTasks::class, ['project' => $record], key('tasks-' . $record->getKey()))
