<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Ganti relasi penanggung_jawabs dengan pics, data lama dipindahkan dengan ID yang sama.
// Kolom `pemohon` sudah ada dari migration 2026_10_06_000001.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('pic_id')->nullable()->after('description')
                ->constrained('pics')->nullOnDelete();
        });

        if (Schema::hasTable('penanggung_jawabs')) {
            // Salin master PJ ke pics, lalu tautkan project
            DB::table('pics')->insertUsing(
                ['id', 'nama', 'jabatan', 'divisi', 'created_at', 'updated_at'],
                DB::table('penanggung_jawabs')->select('id', 'nama', 'jabatan', 'divisi', 'created_at', 'updated_at')
            );
            DB::table('projects')->update(['pic_id' => DB::raw('penanggung_jawab_id')]);
        }

        if (Schema::hasColumn('projects', 'penanggung_jawab_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropConstrainedForeignId('penanggung_jawab_id');
            });
        }

        Schema::dropIfExists('penanggung_jawabs');
    }

    public function down(): void
    {
        Schema::create('penanggung_jawabs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
            $table->timestamps();
        });

        DB::table('penanggung_jawabs')->insertUsing(
            ['id', 'nama', 'jabatan', 'divisi', 'created_at', 'updated_at'],
            DB::table('pics')->select('id', 'nama', 'jabatan', 'divisi', 'created_at', 'updated_at')
        );

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('penanggung_jawab_id')->nullable()->after('description')
                ->constrained('penanggung_jawabs')->nullOnDelete();
        });

        DB::table('projects')->update(['penanggung_jawab_id' => DB::raw('pic_id')]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pic_id');
        });
    }
};
