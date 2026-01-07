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
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'quiz_id']);
            $table->dropForeign(['quiz_id']);
            $table->dropColumn('quiz_id');

            $table->morphs('submittable');

            $table->unique(['user_id', 'submittable_id', 'submittable_type'], 'user_submittable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique('user_submittable_unique');
            $table->dropMorphs('submittable');

            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->unique(['user_id', 'quiz_id']);
        });
    }
};