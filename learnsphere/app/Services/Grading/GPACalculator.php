<?php

namespace App\Services\Grading;

use App\Models\Enrollment;
use Illuminate\Support\Collection;

/**
 * Calculates GPA (Grade Point Average) for a semester.
 *
 * GPA Formula: Σ(grade_points_earned) / Σ(credit_units)
 */
class GPACalculator
{
    /**
     * Calculate semester GPA for a student.
     *
     * @param Enrollment $enrollment The student enrollment
     * @param string|null $semester The semester (optional filter)
     * @return float The calculated GPA, rounded to 2 decimal places
     */
    public function calculateSemesterGPA(Enrollment $enrollment, ?string $semester = null): float
    {
        $results = $enrollment->courseResults()
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->get();

        return $this->calculateFromResults($results);
    }

    /**
     * Calculate GPA from a collection of course results.
     *
     * @param Collection $results Collection of StudentCourseResult models
     * @return float The calculated GPA
     */
    public function calculateFromResults(Collection $results): float
    {
        if ($results->isEmpty()) {
            return 0.0;
        }

        $totalGradePointsEarned = 0;
        $totalCreditUnits = 0;

        foreach ($results as $result) {
            // Handle both object and array results
            $gradePoint = is_array($result) ? $result['grade_point'] ?? null : $result->grade_point;

            // Only include graded/passed courses
            if ($gradePoint !== null) {
                $gradePointsEarned = is_array($result)
                    ? $result['grade_points_earned'] ?? ($gradePoint * ($result['credit_units'] ?? 3.0))
                    : $result->grade_points_earned ?? ($gradePoint * ($result->credit_units ?? 3.0));

                $creditUnits = is_array($result) ? $result['credit_units'] ?? 3.0 : $result->credit_units ?? 3.0;

                $totalGradePointsEarned += $gradePointsEarned;
                $totalCreditUnits += $creditUnits;
            }
        }

        if ($totalCreditUnits == 0) {
            return 0.0;
        }

        return round($totalGradePointsEarned / $totalCreditUnits, 2);
    }

    /**
     * Validate GPA is within valid range (0.0 - 5.0).
     *
     * @param float $gpa
     * @return float
     */
    public function validateGPA(float $gpa): float
    {
        return max(0.0, min(5.0, $gpa));
    }
}
