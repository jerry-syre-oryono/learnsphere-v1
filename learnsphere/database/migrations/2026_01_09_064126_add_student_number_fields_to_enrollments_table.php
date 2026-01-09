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
            $table->string('student_number')->nullable()->unique()->after('course_id');
            $table->integer('enrollment_year')->nullable()->after('student_number');
            $table->index(['course_id', 'enrollment_year']); // For efficient sequence number generation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'enrollment_year']);
            $table->dropColumn(['student_number', 'enrollment_year']);
        });
    }
};
