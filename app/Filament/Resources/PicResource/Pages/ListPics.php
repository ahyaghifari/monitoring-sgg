<?php

namespace App\Filament\Resources\PicResource\Pages;

use App\Filament\Resources\PicResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPics extends ListRecords
{
    protected static string $resource = PicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah PIC')
                ->modalHeading('Tambah PIC')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal'),
        ];
    }
}
