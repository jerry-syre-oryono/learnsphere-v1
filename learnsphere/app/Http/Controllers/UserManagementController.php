<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\Grading\GradingService;

class UserManagementController extends Controller
{
    protected ProgressService $progressService;
    protected GradingService $gradingService;

    public function __construct(ProgressService $progressService, GradingService $gradingService)
    {
        $this->progressService = $progressService;
        $this->gradingService = $gradingService;
    }

    public function index(Request $request)
    {
        // Get all students with their enrollments, courses, and submissions
        $students = User::role('student')
            ->with([
                'enrollments.course',
                'submissions.submittable',
                'submissions' => function ($query) {
                    $query->whereNotNull('score');
                }
            ])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->course, function ($query) use ($request) {
                $query->whereHas('enrollments', function ($q) use ($request) {
                    $q->where('course_id', $request->course);
                });
            })
            ->paginate(20);

        // Calculate progress and grades for each student
        $students->getCollection()->transform(function ($student) {
            // Calculate overall progress for each enrolled course
            $student->course_data = $student->enrollments->map(function ($enrollment) use ($student) {
                $course = $enrollment->course;
                $progress = $this->progressService->getOverallCourseProgress($student, $course);

                return [
                    'course' => $course,
                    'enrollment' => $enrollment,
                    'progress' => $progress,
                    'student_number' => $enrollment->student_number,
                ];
            });

            // Get grade report for overall GPA
            $gradeReport = $this->gradingService->getCompleteGradeReport($student);
            $student->overall_gpa = $gradeReport['cgpa'] ?? null;

            return $student;
        });

        $courses = Course::published()->get();

        return view('admin.user-management.index', compact('students', 'courses'));
    }

    public function show(User $user)
    {
        // Ensure user is a student
        if (!$user->hasRole('student')) {
            abort(404);
        }

        $user->load([
            'enrollments.course.modules.lessons',
            'submissions.submittable',
            'completedLessons'
        ]);

        // Get complete grade report
        $gradeReport = $this->gradingService->getCompleteGradeReport($user);

        // Calculate detailed progress
        $courseProgress = $user->enrollments->map(function ($enrollment) use ($user) {
            $course = $enrollment->course;
            $progress = $this->progressService->getOverallCourseProgress($user, $course);
            return [
                'course' => $course,
                'enrollment' => $enrollment,
                'progress' => $progress,
            ];
        });

        return view('admin.user-management.show', compact('user', 'courseProgress', 'gradeReport'));
    }

    public function destroy(User $user)
    {
        // Ensure user is a student
        if (!$user->hasRole('student')) {
            abort(404);
        }

        // Soft delete or deactivate the student
        $user->delete();

        return redirect()->route('admin.user-management.index')
            ->with('success', 'Student has been removed successfully.');
    }
}