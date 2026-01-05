<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Assessments can be attached to modules or lessons, supporting quizzes and exams.
     */
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('quiz'); // quiz, exam

            // Polymorphic relationship to module or lesson
            $table->morphs('assessable'); // assessable_type, assessable_id

            // Configuration
            $table->integer('time_limit')->unsigned()->default(0); // in minutes, 0 = no limit
            $table->integer('max_attempts')->default(1);
            $table->boolean('randomize_questions')->default(false);
            $table->integer('questions_per_attempt')->nullable(); // For question pools
            $table->decimal('passing_score', 5, 2)->default(60.00); // Percentage
            $table->decimal('weight', 5, 2)->default(1.00); // Weight for final grade calculation

            // Availability
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->boolean('is_published')->default(false);

            // Grading
            $table->boolean('auto_grade')->default(true); // False for manual grading (essays)
            $table->boolean('show_answers_after_submit')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
