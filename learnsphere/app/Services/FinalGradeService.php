<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Assessment;
use App\Services\Grading\GradingService;
use App\Services\Grading\AssessmentWeightValidator;
use App\Services\Grading\ExamThresholdEnforcer;

/**
 * Final Grade Service
 *
 * Calculates final course grade from assessment components with validation:
 * - Validates assessment weights sum to 100
 * - Enforces NCHE exam threshold rule (< 40% = F)
 * - Applies weighted average calculation
 * - Returns normalized 0-100 percentage
 *
 * NCHE Compliance:
 * - Pass mark: 50%
 * - Exam threshold: 40%
 * - All weights must sum to 100%
 */
class FinalGradeService
{
    protected GradingService $gradingService;
    protected AssessmentWeightValidator $weightValidator;
    protected ExamThresholdEnforcer $examEnforcer;

    public function __construct(
        GradingService $gradingService,
        AssessmentWeightValidator $weightValidator,
        ExamThresholdEnforcer $examEnforcer
    ) {
        $this->gradingService = $gradingService;
        $this->weightValidator = $weightValidator;
        $this->examEnforcer = $examEnforcer;
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

    /**
     * Calculate final grade for a student in a course.
     *
     * Process:
     * 1. Validate assessment weights sum to 100
     * 2. Calculate weighted average from submissions
     * 3. Apply exam threshold rule (< 40% = F)
     * 4. Return final percentage (0-100)
     *
     * @param User $user
     * @param Course $course
     * @return float Final percentage mark (0-100), rounded to 2 decimals
     * @throws \InvalidArgumentException If weights validation fails
     */
    public function calculateFinalGrade(User $user, Course $course): float
    {
        // STEP 1: Validate weights
        $validationResult = $this->weightValidator->validate($course, 100.0);

        if (!$validationResult['valid']) {
            throw new \InvalidArgumentException(
                'Assessment weight validation failed: ' .
                implode('; ', $validationResult['errors'])
            );
        }

        // STEP 2: Calculate weighted average
        $finalGrade = $this->calculateWeightedAverage($user, $course);

        // Ensure percentage is within valid range
        $finalGrade = max(0, min(100, $finalGrade));
        $finalGrade = round($finalGrade, 2);

        // STEP 3: Apply exam threshold enforcement
        $enforceResult = $this->examEnforcer->enforce($finalGrade, $user, $course);

        if ($enforceResult['was_enforced']) {
            \Log::warning('Exam threshold enforced', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'original_grade' => $enforceResult['original_grade'],
                'exam_percentage' => $enforceResult['exam_percentage'],
                'reason' => $enforceResult['audit_reason'],
            ]);
        }

        return $enforceResult['final_grade'];
    }

    /**
     * Calculate weighted average from submission percentages.
     * Does NOT apply exam threshold - that's done in calculateFinalGrade.
     *
     * @param User $user
     * @param Course $course
     * @return float Weighted percentage (0-100)
     */
    private function calculateWeightedAverage(User $user, Course $course): float
    {
        $finalGrade = 0;
        $totalWeight = 0;

        $assessableItems = $course->getAssessableItemsAttribute() ?? collect();

        foreach ($assessableItems as $item) {
            $itemWeight = (float)($item->weight ?? 0);

            // Skip items with no weight
            if ($itemWeight <= 0) {
                continue;
            }

            // Get latest submission for this item
            $submission = Submission::where('user_id', $user->id)
                ->where('submittable_type', get_class($item))
                ->where('submittable_id', $item->id)
                ->latest()
                ->first();

            if ($submission) {
                // Calculate percentage from submission
                $percentage = $submission->percentage ?? 0;

                if ($submission->percentage === null && $submission->max_score) {
                    $percentage = ($submission->score / $submission->max_score) * 100;
                }

                // Clamp percentage to 0-100
                $percentage = max(0, min(100, round($percentage, 2)));

                // Add weighted contribution
                $finalGrade += ($percentage / 100) * $itemWeight;
                $totalWeight += $itemWeight;
            }
        }

        // If no submissions, return 0
        if ($totalWeight === 0) {
            return 0;
        }

        // Normalize by total weight (should be 100 if validation passed)
        return round(($finalGrade / $totalWeight) * 100, 2);
    }
}
