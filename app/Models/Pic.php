<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Master data PIC (person in charge) project
class Pic extends Model
{
    protected $fillable = [
        'nama',
        'jabatan',
        'divisi',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
