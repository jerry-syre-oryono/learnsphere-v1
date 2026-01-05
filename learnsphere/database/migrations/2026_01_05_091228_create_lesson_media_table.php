<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Stores media files (PDFs, images, docs) for lessons in structured directories.
     */
    public function up(): void
    {
        Schema::create('lesson_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('filename'); // Original filename
            $table->string('disk')->default('private'); // Storage disk
            $table->string('path'); // Full path: courses/{course_id}/modules/{module_id}/lessons/{lesson_id}/filename
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->string('type')->default('document'); // document, image, video, other
            $table->string('title')->nullable(); // Display title
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_media');
    }
};
