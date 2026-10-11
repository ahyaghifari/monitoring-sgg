<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

// Master data PIC (person in charge) project
class Pic extends Model
{
    protected $fillable = [
        'user_id',
        'nama',
        'foto',
        'jabatan',
        'divisi',
    ];

    // Event: bersihkan file foto di disk public saat diganti atau PIC dihapus
    protected static function booted(): void
    {
        static::updated(function (Pic $pic) {
            $old = $pic->getOriginal('foto');

            if ($pic->wasChanged('foto') && $old) {
                Storage::disk('public')->delete($old);
            }
        });

        static::deleting(function (Pic $pic) {
            if ($pic->foto) {
                Storage::disk('public')->delete($pic->foto);
            }
        });
    }

    // Akun login PIC (opsional)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // Project yang menempatkan PIC ini sebagai PIC kedua
    public function projectsKedua(): HasMany
    {
        return $this->hasMany(Project::class, 'pic_kedua_id');
    }
}
