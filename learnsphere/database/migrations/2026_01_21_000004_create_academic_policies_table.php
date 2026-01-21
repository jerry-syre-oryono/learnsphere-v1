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
        Schema::create('academic_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_code')->unique(); // PASS_MARK, RETAKE_CAP, GRAD_CGPA
            $table->string('policy_name');
            $table->text('description');
            $table->string('value')->nullable();
            $table->enum('policy_type', ['regulation', 'guideline', 'requirement'])->default('regulation');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_policies');
    }
};
