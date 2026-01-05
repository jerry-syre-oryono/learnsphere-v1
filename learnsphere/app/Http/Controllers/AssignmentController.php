<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AssignmentController extends Controller
{
    /**
     * Stream the assignment attachment.
     */
    public function stream(Request $request, Assignment $assignment)
    {
        if (!$assignment->attachment_path) {
            abort(404);
        }

        $user = $request->user();
        $course = $assignment->module->course;

        // Check enrollment/ownership
        $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
        $isInstructor = $course->instructor_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (!$isEnrolled && !$isInstructor && !$isAdmin) {
            abort(403, 'Access denied');
        }

        $disk = 'public'; // Assignments are currently stored in public disk (attachments folder)
        $fullPath = Storage::disk($disk)->path($assignment->attachment_path);

        if (!File::exists($fullPath)) {
            abort(404, 'File not found');
        }

        $mimeType = File::mimeType($fullPath);
        $filename = $assignment->attachment_name ?: basename($assignment->attachment_path);

        // Add extension if missing from custom name
        if (!str_contains($filename, '.')) {
            $filename .= '.' . pathinfo($assignment->attachment_path, PATHINFO_EXTENSION);
        }

        $disposition = $request->has('download') ? 'attachment' : 'inline';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }
}
