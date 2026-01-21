<?php

namespace App\Livewire\Student;

use App\Services\Grading\GradingService;
use App\Services\ProgressService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profile extends Component
{
    protected ProgressService $progressService;
    protected GradingService $gradingService;

    public function boot(ProgressService $progressService, GradingService $gradingService): void
    {
        $this->progressService = $progressService;
        $this->gradingService = $gradingService;
    }

    public function render()
    {
        $user = Auth::user();

        // Load user data with relationships
        $user->load([
            'enrollments.course.modules.lessons',
            'enrollments.courseResults',
            'submissions.submittable',
            'completedLessons'
        ]);

        // Get complete grade report
        $gradeReport = $this->gradingService->getCompleteGradeReport($user);

        // Calculate course progress
        $courseProgress = $user->enrollments->map(function ($enrollment) use ($user, $gradeReport) {
            $course = $enrollment->course;
            $progress = $this->progressService->getOverallCourseProgress($user, $course);
            
            // Find the corresponding course result from the enrollment
            $courseResult = $enrollment->courseResults->first();

            return [
                'course' => $course,
                'enrollment' => $enrollment,
                'progress' => $progress,
                'grade' => $courseResult ? $courseResult->letter_grade : null,
                'grade_point' => $courseResult ? $courseResult->grade_point : null,
                'student_number' => $enrollment->student_number,
                'enrollment_year' => $enrollment->enrollment_year,
            ];
        });

        return view('livewire.student.profile', compact('user', 'courseProgress', 'gradeReport'));
    }
}
