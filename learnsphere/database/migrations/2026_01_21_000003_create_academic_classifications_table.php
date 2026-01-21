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
        Schema::create('academic_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_level_id')
                ->constrained('program_levels')
                ->onDelete('cascade');
            $table->decimal('min_cgpa', 3, 2);
            $table->decimal('max_cgpa', 3, 2)->nullable();
            $table->string('classification')->nullable(); // Distinction, Credit, Pass
            $table->string('class')->nullable();          // First Class, Second Class Upper
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['program_level_id', 'min_cgpa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_classifications');
    }
};
