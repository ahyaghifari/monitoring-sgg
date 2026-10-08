<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectChecklist extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    // Tambah/ubah/hapus tugas langsung menghitung ulang status project induk
    protected static function booted(): void
    {
        static::saved(fn (ProjectChecklist $c) => $c->project?->syncStatus());
        static::deleted(fn (ProjectChecklist $c) => $c->project?->syncStatus());
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
