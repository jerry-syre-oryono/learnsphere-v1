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
        Schema::create('program_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Diploma, Degree, Certificate
            $table->string('code')->unique(); // DIPL, DEG, CERT
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('require_cgpa_for_graduation')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_levels');
    }
};
