<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->text('content')->nullable()->change();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->text('content')->nullable(false)->change();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
        });
    }
};
