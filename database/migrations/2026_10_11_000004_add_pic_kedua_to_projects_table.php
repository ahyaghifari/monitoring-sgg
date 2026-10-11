<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // PIC kedua (opsional); PIC utama tetap di kolom pic_id
            $table->foreignId('pic_kedua_id')->nullable()->after('pic_id')
                ->constrained('pics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pic_kedua_id');
        });
    }
};
