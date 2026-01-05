<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Enhance existing tables with additional assessment features.
     * This migration checks for column existence before adding.
     */
    public function up(): void
    {
        // Add columns to questions table if they don't exist
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'order')) {
                $table->integer('order')->default(0)->after('points');
            }
            if (!Schema::hasColumn('questions', 'explanation')) {
                $table->text('explanation')->nullable()->after('order');
            }
        });

        // Add columns to submissions table if they don't exist
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('submissions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('submissions', 'attempt_number')) {
                $table->integer('attempt_number')->default(1)->after('completed_at');
            }
            if (!Schema::hasColumn('submissions', 'max_score')) {
                $table->decimal('max_score', 8, 2)->nullable()->after('score');
            }
            if (!Schema::hasColumn('submissions', 'percentage')) {
                $table->decimal('percentage', 5, 2)->nullable()->after('max_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $columns = ['order', 'explanation'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('questions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('submissions', function (Blueprint $table) {
            $columns = ['started_at', 'completed_at', 'attempt_number', 'max_score', 'percentage'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('submissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
