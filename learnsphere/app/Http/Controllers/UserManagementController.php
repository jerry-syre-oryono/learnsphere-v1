<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    protected ProgressService $progressService;

    public function __construct(ProgressService $progressService)
    {
        $this->progressService = $progressService;
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
            // Calculate overall progress and grades for each enrolled course
            $student->course_data = $student->enrollments->map(function ($enrollment) use ($student) {
                $course = $enrollment->course;

                // Calculate course progress
                $progress = $this->progressService->getOverallCourseProgress($student, $course);

                // Calculate course grade
                $grade = $this->calculateCourseGrade($student, $course);

                return [
                    'course' => $course,
                    'enrollment' => $enrollment,
                    'progress' => $progress,
                    'grade' => $grade,
                    'student_number' => $enrollment->student_number,
                ];
            });

            // Calculate overall GPA
            $student->overall_gpa = $this->calculateOverallGPA($student);

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

        // Calculate detailed progress and grades
        $courseProgress = $user->enrollments->map(function ($enrollment) use ($user) {
            $course = $enrollment->course;

            $progress = $this->progressService->getOverallCourseProgress($user, $course);
            $grade = $this->calculateCourseGrade($user, $course);

            return [
                'course' => $course,
                'enrollment' => $enrollment,
                'progress' => $progress,
                'grade' => $grade,
                'student_number' => $enrollment->student_number,
                'enrollment_year' => $enrollment->enrollment_year,
            ];
        });

        $overallGPA = $this->calculateOverallGPA($user);

        return view('admin.user-management.show', compact('user', 'courseProgress', 'overallGPA'));
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

    protected function calculateCourseGrade(User $user, Course $course)
    {
        $submissions = $user->submissions()
            ->whereHas('submittable', function ($query) use ($course) {
                $query->whereHas('module', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                });
            })
            ->whereNotNull('percentage')
            ->get();

        if ($submissions->isEmpty()) {
            return null;
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($submissions as $submission) {
            $weight = $submission->submittable->weight ?? 1;
            $totalWeightedScore += ($submission->percentage / 100) * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight === 0) {
            return null;
        }

        return round(($totalWeightedScore / $totalWeight) * 100, 1);
    }

    protected function calculateOverallGPA(User $user)
    {
        $grades = [];

        foreach ($user->enrollments as $enrollment) {
            $grade = $this->calculateCourseGrade($user, $enrollment->course);
            if ($grade !== null) {
                $grades[] = $grade;
            }
        }

        if (empty($grades)) {
            return null;
        }

        return round(array_sum($grades) / count($grades), 1);
    }
}