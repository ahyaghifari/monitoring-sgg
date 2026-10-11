<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    // Akun PIC hanya kelola tugas di beranda; panel admin untuk Super Admin dan Admin/Staff
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role !== UserRole::PIC;
    }

    public function pic(): HasOne
    {
        return $this->hasOne(Pic::class);
    }

    // Super Admin boleh mengelola tugas semua project; PIC hanya project miliknya
    public function canManageProject(Project $project): bool
    {
        $picId = $this->pic?->id;

        return $this->isSuperAdmin()
            || ($picId !== null && in_array($picId, [$project->pic_id, $project->pic_kedua_id], true));
    }

    // Super Admin boleh mengakses semua menu admin
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }
}
