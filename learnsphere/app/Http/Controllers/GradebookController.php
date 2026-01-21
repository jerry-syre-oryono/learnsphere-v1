<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

use App\Services\FinalGradeService;

class GradebookController extends Controller
{
    protected FinalGradeService $finalGradeService;

    public function __construct(FinalGradeService $finalGradeService)
    {
        $this->finalGradeService = $finalGradeService;
    }

    public function index(Request $request, Course $course = null)
    {
        $user = $request->user();

        // If viewing a specific course gradebook
        if ($course) {
            // Check if user can access this course's gradebook
            if (!$this->canAccessCourseGradebook($user, $course)) {
                abort(403, 'You do not have permission to view this course\'s gradebook.');
            }

            $courses = collect([$course])->load([
                'assignments.module',
                'students.submissions.submittable',
                'students.submissions.quiz',
                'enrollments'
            ]);

            return view('admin.gradebook.index', compact('courses', 'course'));
        }

        // Admin gradebook - show all courses or filter for instructors
        $query = Course::with([
            'assignments.module',
            'students.submissions.submittable',
            'students.submissions.quiz',
            'enrollments'
        ]);

        // If user is instructor, only show their courses
        if ($user->hasRole('instructor') && !$user->hasRole('admin')) {
            $query->where('instructor_id', $user->id);
        }

        $courses = $query->get();

        return view('admin.gradebook.index', compact('courses'));
    }

    /**
     * Process final grades for all students in a course.
     *
     * @param Request $request
     * @param Course $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processGrades(Request $request, Course $course)
    {
        if (!$this->canAccessCourseGradebook($request->user(), $course)) {
            abort(403, 'You do not have permission to process grades for this course.');
        }

        $this->finalGradeService->processFinalGradesForCourse($course);

        return redirect()->back()->with('status', 'Final grades have been calculated and processed successfully.');
    }

    public function export(Course $course)
    {
        $user = request()->user();

        // Check if user can access this course's gradebook
        if (!$this->canAccessCourseGradebook($user, $course)) {
            abort(403, 'You do not have permission to export this course\'s gradebook.');
        }

        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality not yet implemented']);
    }

    protected function canAccessCourseGradebook($user, Course $course)
    {
        // Admins can access all courses
        if ($user->hasRole('admin')) {
            return true;
        }

        // Instructors can only access courses they created
        if ($user->hasRole('instructor') && $course->instructor_id === $user->id) {
            return true;
        }

        return false;
    }
}
