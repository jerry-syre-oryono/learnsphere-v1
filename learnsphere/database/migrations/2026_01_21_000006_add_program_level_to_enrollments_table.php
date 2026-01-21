<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'program_level_id')) {
                $table->foreignId('program_level_id')
                    ->nullable()
                    ->constrained('program_levels')
                    ->onDelete('set null')
                    ->after('course_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'program_level_id')) {
                $table->dropForeignIdFor('ProgramLevel');
                $table->dropColumn('program_level_id');
            }
        });
    }
};
