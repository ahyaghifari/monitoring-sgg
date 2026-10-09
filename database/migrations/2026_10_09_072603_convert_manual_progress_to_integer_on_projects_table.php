<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Progress manual jadi satu angka 0-100; daftar sub-task JSON lama dikonversi ke totalnya (maks 100).
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('manual_progress_value')->default(0)->after('progress_mode');
        });

        DB::table('projects')->whereNotNull('manual_progress')->orderBy('id')->each(function (object $project) {
            $total = collect(json_decode($project->manual_progress, true) ?: [])->sum(fn ($item) => (int) ($item['percentage'] ?? 0));

            DB::table('projects')->where('id', $project->id)->update(['manual_progress_value' => min(100, $total)]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('manual_progress');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('manual_progress_value', 'manual_progress');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('manual_progress_list')->nullable()->after('progress_mode');
        });

        DB::table('projects')->where('manual_progress', '>', 0)->orderBy('id')->each(function (object $project) {
            DB::table('projects')->where('id', $project->id)->update([
                'manual_progress_list' => json_encode([['percentage' => $project->manual_progress, 'task_name' => null]]),
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('manual_progress');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('manual_progress_list', 'manual_progress');
        });
    }
};
