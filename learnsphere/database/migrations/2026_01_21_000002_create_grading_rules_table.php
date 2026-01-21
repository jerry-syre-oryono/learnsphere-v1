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
        Schema::create('grading_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_level_id')
                ->constrained('program_levels')
                ->onDelete('cascade');
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->string('letter_grade');
            $table->decimal('grade_point', 3, 1);
            $table->text('description')->nullable();
            $table->timestamps();

            // Ensure unique grading rules per program level
            $table->unique(['program_level_id', 'min_percentage', 'max_percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_rules');
    }
};
