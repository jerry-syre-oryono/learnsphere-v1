<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LessonMedia extends Model
{
    use HasFactory;

    protected $table = 'lesson_media';

    protected $guarded = [];

    protected $casts = [
        'size' => 'integer',
        'order' => 'integer',
    ];

    // Allowed file types for security
    public const ALLOWED_MIME_TYPES = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'video' => ['video/mp4', 'video/webm', 'video/quicktime'],
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the full storage path for this media.
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    /**
     * Get the URL for this media.
     * For private disk, returns a route to the streaming controller.
     * For public disk, returns the direct URL.
     */
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return asset('storage/' . $this->path);
        }

        // For private files, use the streaming route
        return route('lesson.media.stream', $this->id);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Determine the type based on MIME type.
     */
    public static function getTypeFromMime(string $mimeType): string
    {
        foreach (self::ALLOWED_MIME_TYPES as $type => $mimes) {
            if (in_array($mimeType, $mimes)) {
                return $type;
            }
        }
        return 'other';
    }

    /**
     * Validate MIME type is allowed.
     */
    public static function isAllowedMimeType(string $mimeType): bool
    {
        foreach (self::ALLOWED_MIME_TYPES as $mimes) {
            if (in_array($mimeType, $mimes)) {
                return true;
            }
        }
        return false;
    }
}
