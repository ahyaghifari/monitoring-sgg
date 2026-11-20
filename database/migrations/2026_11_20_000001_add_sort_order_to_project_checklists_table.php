<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_checklists', function (Blueprint $table) {
            // Urutan tugas di daftar (diatur lewat drag & drop)
            $table->unsignedInteger('sort_order')->default(0)->after('is_completed');
        });

        // Tugas lama diurutkan sesuai urutan dibuat (id) per project
        DB::table('project_checklists')
            ->orderBy('project_id')
            ->orderBy('id')
            ->get(['id', 'project_id'])
            ->groupBy('project_id')
            ->each(function ($tasks) {
                foreach ($tasks->values() as $index => $task) {
                    DB::table('project_checklists')->where('id', $task->id)->update(['sort_order' => $index + 1]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('project_checklists', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
