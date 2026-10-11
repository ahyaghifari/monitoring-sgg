<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

// Peran akun admin; HasLabel agar Filament otomatis memakai label di Select & badge
enum UserRole: string implements HasLabel
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case PIC = 'pic';

    public function getLabel(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin / Staff',
            self::PIC => 'PIC (kelola tugas di beranda)',
        };
    }
}
