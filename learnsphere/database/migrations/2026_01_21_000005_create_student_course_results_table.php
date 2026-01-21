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
        Schema::create('student_course_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->onDelete('cascade');
            $table->foreignId('course_id')
                ->constrained('courses')
                ->onDelete('cascade');
            $table->decimal('final_mark', 5, 2)->nullable();
            $table->string('letter_grade')->nullable();
            $table->decimal('grade_point', 3, 1)->nullable();
            $table->decimal('grade_points_earned', 5, 2)->nullable();
            $table->decimal('credit_units', 3, 1)->default(3.0);
            $table->string('semester')->nullable();
            $table->boolean('is_retake')->default(false);
            $table->boolean('was_capped')->default(false);
            $table->string('original_grade')->nullable();
            $table->string('capped_grade')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            // Ensure unique result per enrollment-course pair per semester
            $table->unique(['enrollment_id', 'course_id', 'semester']);
            $table->index('enrollment_id');
            $table->index('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_course_results');
    }
};
