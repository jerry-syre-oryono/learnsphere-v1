<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Tracks individual question responses within a submission for detailed analytics.
     */
    public function up(): void
    {
        Schema::create('question_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->json('answer')->nullable(); // The student's answer (supports all formats)
            $table->boolean('is_correct')->nullable(); // Null for ungraded
            $table->decimal('points_earned', 8, 2)->default(0);
            $table->text('feedback')->nullable(); // Instructor feedback for this specific question
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_responses');
    }
};
