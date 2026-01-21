<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Assessment;
use App\Services\Grading\GradingService;

class FinalGradeService
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Process final grades for all students in a course.
     *
     * @param Course $course
     * @return void
     */
    public function processFinalGradesForCourse(Course $course): void
    {
        $enrollments = $course->enrollments()->with('user')->get();

        foreach ($enrollments as $enrollment) {
            $user = $enrollment->user;
            $finalPercentage = $this->calculateFinalGrade($user, $course);

            // Assuming a semester can be determined or is fixed for this context.
            // This might need to be passed in or determined dynamically.
            $semester = now()->year . '-' . (now()->year + 1) . '-1';

            $this->gradingService->processStudentGrade(
                enrollment: $enrollment,
                percentageMark: $finalPercentage,
                creditUnits: $course->credit_units ?? 3.0, // Assuming a default, should be on course model
                isRetake: false, // This would need to be determined from student history
                semester: $semester
            );
        }
    }

    public function calculateFinalGrade(User $user, Course $course): float
    {
        $finalGrade = 0;
        $totalWeight = 0;

        $assessableItems = $course->getAssessableItemsAttribute();

        foreach ($assessableItems as $item) {
            $submission = null;
            $itemWeight = $item->weight ?? 0;

            if ($itemWeight > 0) {
                if ($item instanceof Assessment || $item instanceof Assignment) {
                    $submission = Submission::where('user_id', $user->id)
                        ->where('submittable_type', get_class($item))
                        ->where('submittable_id', $item->id)
                        ->latest()
                        ->first();
                }

                if ($submission) {
                    $percentage = $submission->percentage ?? ($submission->score / $item->max_score) * 100;
                    if ($percentage !== null) {
                        $finalGrade += ($percentage / 100) * $itemWeight;
                    }
                }
                $totalWeight += $itemWeight;
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return round(($finalGrade / $totalWeight) * 100, 2);
    }
}
