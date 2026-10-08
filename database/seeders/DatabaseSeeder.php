<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun default; updateOrCreate agar aman dijalankan berulang
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Super Admin', 'password' => 'password', 'role' => UserRole::SUPER_ADMIN],
        );

        User::updateOrCreate(
            ['email' => 'staff@gmail.com'],
            ['name' => 'Staff', 'password' => 'password', 'role' => UserRole::ADMIN],
        );
    }
}
