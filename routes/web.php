<?php

use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PicSessionController;
use App\Http\Controllers\ProjectTaskController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
//  Halaman utama — Single Page Monitoring Dashboard (publik, read-only).
//  Tambah/Ubah/Hapus data dilakukan melalui Admin Panel Filament.
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', [MonitoringController::class, 'index'])->name('monitoring');

// ─────────────────────────────────────────────────────────────────────────────
//  Akun PIC: login dari beranda, lalu kelola tugas project miliknya (JSON/fetch).
// ─────────────────────────────────────────────────────────────────────────────
Route::post('/masuk', [PicSessionController::class, 'store'])->middleware('throttle:6,1')->name('pic.login');

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [PicSessionController::class, 'destroy'])->name('pic.logout');
    Route::post('/projects/{project}/tasks', [ProjectTaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{checklist}', [ProjectTaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{checklist}', [ProjectTaskController::class, 'destroy'])->name('tasks.destroy');
});
