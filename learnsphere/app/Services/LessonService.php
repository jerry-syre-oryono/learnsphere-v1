<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LessonService
{
    /**
     * Create a new lesson within a module.
     */
    public function create(Module $module, array $data): Lesson
    {
        $order = $data['order'] ?? ($module->lessons()->max('order') + 1);

        return $module->lessons()->create([
            'course_id' => $module->course_id,
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'content_type' => $data['content_type'] ?? 'text',
            'video_url' => $data['video_url'] ?? null,
            'order' => $order,
        ]);
    }

    /**
     * Update an existing lesson.
     */
    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update(array_filter([
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
            'content_type' => $data['content_type'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'order' => $data['order'] ?? null,
        ], fn($value) => $value !== null));

        return $lesson->fresh();
    }

    /**
     * Upload a file to a lesson.
     * Files are stored in: storage/app/courses/{course_id}/modules/{module_id}/lessons/{lesson_id}/
     */
    public function uploadMedia(Lesson $lesson, UploadedFile $file, array $metadata = []): LessonMedia
    {
        // Security: Validate MIME type
        $mimeType = $file->getMimeType();
        if (!LessonMedia::isAllowedMimeType($mimeType)) {
            throw new \InvalidArgumentException("File type not allowed: {$mimeType}");
        }

        // Security: Sanitize filename to prevent path traversal
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeFilename = Str::slug($originalName) . '-' . Str::random(8) . '.' . $extension;

        // Build structured path
        $storagePath = $lesson->storage_path;
        $fullPath = $storagePath . '/' . $safeFilename;

        // Store file (private disk by default for security)
        $disk = $metadata['disk'] ?? 'private';
        Storage::disk($disk)->putFileAs($storagePath, $file, $safeFilename);

        // Create media record
        return LessonMedia::create([
            'lesson_id' => $lesson->id,
            'filename' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $fullPath,
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'type' => LessonMedia::getTypeFromMime($mimeType),
            'title' => $metadata['title'] ?? $originalName,
            'description' => $metadata['description'] ?? null,
            'order' => $metadata['order'] ?? ($lesson->media()->max('order') + 1),
        ]);
    }

    /**
     * Delete a media file and its record.
     */
    public function deleteMedia(LessonMedia $media): bool
    {
        // Delete file from storage
        Storage::disk($media->disk)->delete($media->path);

        // Delete record
        return $media->delete();
    }

    /**
     * Reorder lessons within a module.
     */
    public function reorder(Module $module, array $orderedIds): void
    {
        DB::transaction(function () use ($module, $orderedIds) {
            foreach ($orderedIds as $index => $lessonId) {
                $module->lessons()->where('id', $lessonId)->update(['order' => $index + 1]);
            }
        });
    }

    /**
     * Delete a lesson and all its media.
     */
    public function delete(Lesson $lesson): bool
    {
        return DB::transaction(function () use ($lesson) {
            // Delete all media files
            foreach ($lesson->media as $media) {
                $this->deleteMedia($media);
            }

            // Delete the lesson's storage directory
            Storage::disk('private')->deleteDirectory($lesson->storage_path);

            return $lesson->delete();
        });
    }
}
