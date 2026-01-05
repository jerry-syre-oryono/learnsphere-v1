<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonMedia;
use App\Models\Module;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    use AuthorizesRequests;

    protected LessonService $lessonService;

    public function __construct(LessonService $lessonService)
    {
        $this->lessonService = $lessonService;
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(Request $request, Module $module)
    {
        $this->authorize('update', $module->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'content_type' => 'nullable|string|in:text,video,pdf,doc',
            'video_url' => 'nullable|url',
            'order' => 'nullable|integer',
        ]);

        $lesson = $this->lessonService->create($module, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Lesson created successfully',
            'lesson' => $lesson,
        ]);
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson->course);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'content_type' => 'nullable|string|in:text,video,pdf,doc',
            'video_url' => 'nullable|url',
            'order' => 'nullable|integer',
        ]);

        $lesson = $this->lessonService->update($lesson, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully',
            'lesson' => $lesson,
        ]);
    }

    /**
     * Upload a file to a lesson.
     * Security: Validates MIME types and prevents path traversal.
     */
    public function uploadMaterial(Request $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson->course);

        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $media = $this->lessonService->uploadMedia(
                $lesson,
                $request->file('file'),
                [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'disk' => 'private', // Always use private for security
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'media' => $media,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get a signed URL to access a private media file.
     * Security: Checks enrollment before granting access.
     */
    public function streamMedia(Request $request, LessonMedia $media)
    {
        return $this->serveFile($request, $media->lesson->course, $media->disk, $media->path, $media->mime_type, $media->filename);
    }

    /**
     * Stream the main attachment of a lesson.
     */
    public function streamAttachment(Request $request, Lesson $lesson)
    {
        if (!$lesson->attachment_path) {
            abort(404);
        }

        $disk = str_contains($lesson->attachment_path, 'attachments/') ? 'public' : 'private';

        // Guess mime type if not stored
        $mimeType = \Illuminate\Support\Facades\File::mimeType(Storage::disk($disk)->path($lesson->attachment_path));
        $filename = basename($lesson->attachment_path);

        return $this->serveFile($request, $lesson->course, $disk, $lesson->attachment_path, $mimeType, $filename);
    }

    /**
     * Internal helper to serve protected files.
     */
    protected function serveFile(Request $request, Course $course, string $disk, string $path, string $mimeType, string $filename)
    {
        $user = $request->user();

        // Check enrollment/ownership
        $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
        $isInstructor = $course->instructor_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (!$isEnrolled && !$isInstructor && !$isAdmin) {
            abort(403, 'Access denied');
        }

        $fullPath = Storage::disk($disk)->path($path);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }

        $disposition = $request->has('download') ? 'attachment' : 'inline';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }

    /**
     * Delete a media file.
     */
    public function deleteMedia(LessonMedia $media)
    {
        $this->authorize('update', $media->lesson->course);

        $this->lessonService->deleteMedia($media);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Reorder lessons within a module.
     */
    public function reorder(Request $request, Module $module)
    {
        $this->authorize('update', $module->course);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:lessons,id',
        ]);

        $this->lessonService->reorder($module, $validated['order']);

        return response()->json([
            'success' => true,
            'message' => 'Lessons reordered successfully',
        ]);
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $this->authorize('update', $lesson->course);

        $this->lessonService->delete($lesson);

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
        ]);
    }
}
