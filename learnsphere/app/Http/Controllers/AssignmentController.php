<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Submission;

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

    public function submit(Request $request, Assignment $assignment)
    {
        $user = $request->user();
        $course = $assignment->module->course;

        $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
        if (! $isEnrolled) {
            abort(403, 'Only enrolled students can submit assignments.');
        }

        $data = $request->validate([
            'attachment' => 'required|file|mimes:pdf,doc,docx,txt,md,odt,zip,rar,png,jpg,jpeg,gif,xlsx,xls,pptx,ppt,mp4,mov,avi|max:51200', // up to 50MB
        ]);

        $file = $data['attachment'];
        $disk = 'public';
        $path = $file->storeAs('submissions/assignments/' . $assignment->id, 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension(), $disk);

        $submission = Submission::updateOrCreate([
            'user_id' => $user->id,
            'submittable_type' => Assignment::class,
            'submittable_id' => $assignment->id,
        ], [
            'attachment_path' => $path,
            'attachment_name' => $file->getClientOriginalName(),
            'answers' => [],
            'status' => Submission::STATUS_PENDING_REVIEW,
            'max_score' => $assignment->max_score,
        ]);

        return redirect()->back()->with('status', 'Assignment submitted successfully.');
    }
}
