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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('type')->default('quiz')->after('description'); // quiz, exam
            $table->integer('max_attempts')->default(1)->after('time_limit');
            $table->integer('questions_per_attempt')->nullable()->after('randomize');
            $table->decimal('passing_score', 5, 2)->default(60.00)->after('questions_per_attempt'); // Percentage
            $table->decimal('weight', 5, 2)->default(1.00)->after('passing_score'); // Weight for final grade calculation
            $table->timestamp('available_from')->nullable()->after('weight');
            $table->timestamp('available_until')->nullable()->after('available_from');
            $table->boolean('is_published')->default(false)->after('available_until');
            $table->boolean('show_answers_after_submit')->default(false)->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'max_attempts',
                'questions_per_attempt',
                'passing_score',
                'weight',
                'available_from',
                'available_until',
                'is_published',
                'show_answers_after_submit'
            ]);
        });
    }
};
