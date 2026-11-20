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
        'sort_order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Tambah/ubah/hapus tugas langsung menghitung ulang status project induk
    protected static function booted(): void
    {
        // Tugas baru tanpa urutan otomatis ditaruh paling bawah
        static::creating(function (ProjectChecklist $c) {
            if (! $c->sort_order) {
                $c->sort_order = (int) static::where('project_id', $c->project_id)->max('sort_order') + 1;
            }
        });

        static::saved(fn (ProjectChecklist $c) => $c->project?->syncStatus());
        static::deleted(fn (ProjectChecklist $c) => $c->project?->syncStatus());
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
