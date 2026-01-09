<?php

namespace App\Livewire\Student;

use App\Services\ProgressService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profile extends Component
{
    protected ProgressService $progressService;

    public function boot(ProgressService $progressService): void
    {
        $this->progressService = $progressService;
    }

    public function render()
    {
        $user = Auth::user();

        // Load user data with relationships
        $user->load([
            'enrollments.course.modules.lessons',
            'submissions.submittable',
            'completedLessons'
        ]);

        // Calculate course progress and grades
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

        return view('livewire.student.profile', compact('user', 'courseProgress', 'overallGPA'));
    }

    protected function calculateCourseGrade($user, $course)
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

    protected function calculateOverallGPA($user)
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
