<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('progress_mode', ['auto', 'manual'])->default('auto')->after('status');
            // Daftar sub-task manual: [{percentage: int, task_name: ?string}, ...]
            $table->json('manual_progress')->nullable()->after('progress_mode');
            $table->text('catatan_pic')->nullable()->after('pemohon');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['progress_mode', 'manual_progress', 'catatan_pic']);
        });
    }
};
