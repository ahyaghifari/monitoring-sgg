<?php

use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
//  Halaman utama — Single Page Monitoring Dashboard (publik, read-only).
//  Tambah/Ubah/Hapus data dilakukan melalui Admin Panel Filament.
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', [MonitoringController::class, 'index'])->name('monitoring');
