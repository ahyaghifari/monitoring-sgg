<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Ganti kolom teks `penanggung_jawab` dengan relasi ke tabel master penanggung_jawabs.
// Kolom `pemohon` sudah ada dari migration 2026_10_06_000001.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('penanggung_jawab_id')->nullable()->after('description')
                ->constrained('penanggung_jawabs')->nullOnDelete();
        });

        // Pindahkan nama PJ yang sudah diisi manual ke tabel master, lalu tautkan
        $names = DB::table('projects')
            ->whereNotNull('penanggung_jawab')
            ->where('penanggung_jawab', '!=', '')
            ->distinct()
            ->pluck('penanggung_jawab');

        foreach ($names as $nama) {
            $id = DB::table('penanggung_jawabs')->insertGetId([
                'nama'       => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('projects')->where('penanggung_jawab', $nama)->update(['penanggung_jawab_id' => $id]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('penanggung_jawab');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('penanggung_jawab')->nullable()->after('description');
        });

        // Kembalikan nama PJ ke kolom teks
        DB::table('projects')->whereNotNull('penanggung_jawab_id')->update([
            'penanggung_jawab' => DB::raw('(select nama from penanggung_jawabs where penanggung_jawabs.id = projects.penanggung_jawab_id)'),
        ]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penanggung_jawab_id');
        });
    }
};
