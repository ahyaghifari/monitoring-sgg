<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'pic_id',
        'pemohon',
        'catatan_pic',
        'logo',
        'urgency',
        'status',
        'progress_mode',
        'manual_progress',
    ];

    // Checklist (tabel relasi) & persentase manual (kolom angka) terpisah: ganti mode tidak menghapus salah satunya
    protected $casts = [
        'manual_progress' => 'integer',
    ];

    // Event: sinkron status dari tugas & bersihkan file logo di disk public
    protected static function booted(): void
    {
        // Status ikut progress; project auto tanpa tugas tetap pakai status manual dari form
        static::saved(function (Project $project) {
            if ($project->isManualMode() || $project->checklists()->exists()) {
                $project->syncStatus();
            }
        });

        // Menghapus file logo lama dari storage saat logo diganti atau dikosongkan
        static::updated(function (Project $project) {
            $old = $project->getOriginal('logo');

            if ($project->wasChanged('logo') && $old) {
                Storage::disk('public')->delete($old);
            }
        });

        // Menghapus file fisik logo dari storage saat data project dihapus
        static::deleting(function (Project $project) {
            if ($project->logo) {
                Storage::disk('public')->delete($project->logo);
            }
        });
    }

    // Relasi
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(Pic::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(ProjectChecklist::class);
    }

    public function isManualMode(): bool
    {
        return $this->progress_mode === 'manual';
    }

    // Status otomatis: 'Completed' jika progress mode aktif selesai, selain itu 'In Progress'
    public function syncStatus(): void
    {
        if ($this->isManualMode()) {
            $isCompleted = $this->manual_progress === 100;
        } else {
            $total = $this->checklists()->count();
            $isCompleted = $total > 0 && $this->checklists()->where('is_completed', true)->count() === $total;
        }

        $status = $isCompleted ? 'Completed' : 'In Progress';

        // Query langsung: tanpa event (tidak berulang) dan aman dipanggil dari dalam event saved
        if ($this->status !== $status) {
            static::whereKey($this->getKey())->update(['status' => $status]);
            $this->status = $status;
        }
    }

    // Accessor
    // Mode manual: nilai persentase tunggal dari kolom manual_progress.
    // Mode auto: (tugas selesai / total) * 100, 0 jika belum ada tugas;
    // memakai relasi yang sudah di-eager load agar tidak query per kartu.
    protected function progressPercent(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->isManualMode()) {
                    return (int) $this->manual_progress;
                }

                $total = $this->checklists->count();

                if ($total === 0) {
                    return 0;
                }

                $completed = $this->checklists->where('is_completed', true)->count();

                return (int) round(($completed / $total) * 100);
            }
        );
    }
}
