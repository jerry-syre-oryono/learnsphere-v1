<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SubmissionController extends Controller
{
    public function download(Request $request, Submission $submission)
    {
        $user = $request->user();

        $isOwner = $submission->user_id === $user->id;
        $isInstructor = $submission->submittable && $submission->submittable->module->course->instructor_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (! $isOwner && ! $isInstructor && ! $isAdmin) {
            abort(403);
        }

        if (! $submission->attachment_path) {
            abort(404);
        }

        $disk = 'public';
        if (! Storage::disk($disk)->exists($submission->attachment_path)) {
            abort(404);
        }

        $filename = $submission->attachment_name ?: basename($submission->attachment_path);

        // Use Storage download to properly handle file streams and headers
        return Storage::disk($disk)->download($submission->attachment_path, $filename);
    }

    public function grade(Request $request, Submission $submission)
    {
        $user = $request->user();

        $submittable = $submission->submittable;
        if (! $submittable) {
            abort(404);
        }

        $course = null;
        if ($submittable instanceof Assignment) {
            $course = $submittable->module->course;
        }

        $isInstructor = $course && $course->instructor_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (! $isInstructor && ! $isAdmin) {
            abort(403);
        }

        // Calculate max score before validation
        $max = $submission->max_score ?: ($submittable->max_score ?? null);
        if ($max === null) {
             // Handle case where max score is not defined, perhaps default to a high value or error
             // For now, let's assume it should always be defined for grading
             return response()->json(['message' => 'Max score not defined for this submittable.'], 400);
        }

        $data = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $max, // Added max validation
            'feedback' => 'nullable|string',
        ]);

        $submission->score = $data['score'];
        $submission->feedback = $data['feedback'] ?? null;

        if ($max) { // This check is already done above, but keep it for robustness
            $submission->percentage = ($submission->score / $max) * 100;
            $submission->percentage = min(100, $submission->percentage); // Cap percentage at 100
            $submission->max_score = $max;
        }

        $submission->status = Submission::STATUS_PENDING_REVIEW;
        $submission->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Submission graded.',
                'submission' => [
                    'id' => $submission->id,
                    'score' => $submission->score,
                    'percentage' => $submission->percentage,
                    'feedback' => $submission->feedback,
                ],
            ]);
        }

        return redirect()->back()->with('status', 'Submission graded.');
    }
}
