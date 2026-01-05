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
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('content_type')->default('text')->after('title'); // text, video, pdf, doc
            if (!Schema::hasColumn('lessons', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('video_url');
            }
            $table->text('content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['content_type', 'attachment_path']);
            $table->text('content')->nullable(false)->change();
        });
    }
};
