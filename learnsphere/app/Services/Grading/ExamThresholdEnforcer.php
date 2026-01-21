<?php

namespace App\Services\Grading;

use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\Assessment;

/**
 * Exam Threshold Enforcer
 *
 * NCHE Regulation: "Any student who obtains less than 40% in an examination
 * shall automatically fail the course regardless of their continuous assessment."
 *
 * This service ensures:
 * - Exam component is identified from assessments
 * - Exam percentage is checked
 * - If exam < 40%, final grade is set to F (0.0) regardless of weighted average
 * - Override reason is stored in audit trail
 */
class ExamThresholdEnforcer
{
    public const EXAM_THRESHOLD = 40.0;
    public const EXAM_KEYWORDS = ['exam', 'final exam', 'test', 'midterm', 'final assessment'];

    /**
     * Check if exam threshold should enforce fail grade.
     *
     * @param User $student
     * @param Course $course
     * @return array [
     *     'should_enforce' => bool,
     *     'exam_percentage' => float|null,
     *     'exam_name' => string|null,
     *     'reason' => string|null
     * ]
     */
    public function checkExamThreshold(User $student, Course $course): array
    {
        $examComponent = $this->findExamComponent($course);

        if (!$examComponent) {
            return [
                'should_enforce' => false,
                'exam_percentage' => null,
                'exam_name' => null,
                'reason' => 'No exam component found for course',
            ];
        }

        $submission = $this->getLatestSubmission($student, $examComponent);

        if (!$submission) {
            return [
                'should_enforce' => false,
                'exam_percentage' => null,
                'exam_name' => $this->getComponentName($examComponent),
                'reason' => 'No exam submission found',
            ];
        }

        $examPercentage = $submission->percentage ?? ($submission->score / $submission->max_score) * 100;

        // Round to 2 decimals for consistent comparison
        $examPercentage = round($examPercentage, 2);

        if ($examPercentage < self::EXAM_THRESHOLD) {
            return [
                'should_enforce' => true,
                'exam_percentage' => $examPercentage,
                'exam_name' => $this->getComponentName($examComponent),
                'reason' => "Exam score ({$examPercentage}%) is below 40% threshold - NCHE regulation enforced",
            ];
        }

        return [
            'should_enforce' => false,
            'exam_percentage' => $examPercentage,
            'exam_name' => $this->getComponentName($examComponent),
            'reason' => 'Exam threshold not triggered',
        ];
    }

    /**
     * Apply exam threshold to final grade.
     * If exam < 40%, returns 0 (fail) and audit data.
     *
     * @param float $calculatedFinalGrade The weighted grade (before threshold check)
     * @param User $student
     * @param Course $course
     * @return array [
     *     'final_grade' => float,
     *     'was_enforced' => bool,
     *     'exam_percentage' => float|null,
     *     'original_grade' => float|null,
     *     'audit_reason' => string|null
     * ]
     */
    public function enforce(float $calculatedFinalGrade, User $student, Course $course): array
    {
        $check = $this->checkExamThreshold($student, $course);

        if ($check['should_enforce']) {
            return [
                'final_grade' => 0.0, // F grade
                'was_enforced' => true,
                'exam_percentage' => $check['exam_percentage'],
                'original_grade' => $calculatedFinalGrade,
                'audit_reason' => $check['reason'],
            ];
        }

        return [
            'final_grade' => $calculatedFinalGrade,
            'was_enforced' => false,
            'exam_percentage' => $check['exam_percentage'],
            'original_grade' => null,
            'audit_reason' => null,
        ];
    }

    /**
     * Find exam component from course assessments.
     * Searches by name keywords (exam, test, midterm, etc).
     *
     * @param Course $course
     * @return Assessment|null
     */
    private function findExamComponent(Course $course): ?Assessment
    {
        $assessableItems = $course->getAssessableItemsAttribute() ?? collect();

        // Try to find by keyword
        foreach ($assessableItems as $item) {
            if ($item instanceof Assessment) {
                $itemName = strtolower($item->title ?? '');
                foreach (self::EXAM_KEYWORDS as $keyword) {
                    if (str_contains($itemName, $keyword)) {
                        return $item;
                    }
                }
            }
        }

        // Alternative: find by type (if Assessment has type field)
        foreach ($assessableItems as $item) {
            if ($item instanceof Assessment && $item->type === Assessment::TYPE_EXAM) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Get the latest submission for an exam component.
     *
     * @param User $student
     * @param Assessment $exam
     * @return Submission|null
     */
    private function getLatestSubmission(User $student, Assessment $exam): ?Submission
    {
        return Submission::where('user_id', $student->id)
            ->where('submittable_type', get_class($exam))
            ->where('submittable_id', $exam->id)
            ->latest()
            ->first();
    }

    /**
     * Get human-readable name for component.
     *
     * @param Assessment|mixed $component
     * @return string
     */
    private function getComponentName($component): string
    {
        if ($component instanceof Assessment) {
            return $component->title ?? "Exam {$component->id}";
        }
        return "Exam component";
    }

    /**
     * Validate that course has exam component configured.
     *
     * @param Course $course
     * @throws \RuntimeException
     * @return Assessment
     */
    public function assertHasExam(Course $course): Assessment
    {
        $exam = $this->findExamComponent($course);

        if (!$exam) {
            throw new \RuntimeException(
                "Course '{$course->name}' does not have an exam component configured. " .
                "Cannot enforce 40% exam threshold rule."
            );
        }

        return $exam;
    }
}
