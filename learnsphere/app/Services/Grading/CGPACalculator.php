<?php

namespace App\Services\Grading;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Calculates CGPA (Cumulative Grade Point Average) for a student across all semesters.
 *
 * CGPA Formula: Σ(all grade_points_earned) / Σ(all credit_units)
 *
 * NCHE Regulation: A candidate shall not graduate with a CGPA below 2.00.
 */
class CGPACalculator
{
    /**
     * Calculate cumulative GPA for a student.
     *
     * @param User $student The student
     * @return float The calculated CGPA, rounded to 2 decimal places
     */
    public function calculateForStudent(User $student): float
    {
        // Get all course results for all enrollments
        $allResults = [];
        foreach ($student->enrollments as $enrollment) {
            $results = $enrollment->courseResults()->get();
            $allResults = array_merge($allResults, $results->toArray());
        }

        return $this->calculateFromResults(collect($allResults));
    }

    /**
     * Calculate CGPA from a collection of course results.
     *
     * @param Collection $results Collection of StudentCourseResult models
     * @return float The calculated CGPA
     */
    public function calculateFromResults(Collection $results): float
    {
        if ($results->isEmpty()) {
            return 0.0;
        }

        $totalGradePointsEarned = 0;
        $totalCreditUnits = 0;

        foreach ($results as $result) {
            // Include all registered courses (even failed ones contribute 0.0)
            if (isset($result['grade_points_earned']) && $result['grade_points_earned'] !== null) {
                $totalGradePointsEarned += $result['grade_points_earned'];
                $totalCreditUnits += $result['credit_units'] ?? 3.0;
            }
        }

        if ($totalCreditUnits == 0) {
            return 0.0;
        }

        $cgpa = $totalGradePointsEarned / $totalCreditUnits;

        // Validate CGPA is within valid range (0.0 - 5.0)
        return max(0.0, min(5.0, round($cgpa, 2)));
    }

    /**
     * Get graduation readiness based on CGPA.
     *
     * NCHE Regulation: Minimum CGPA for graduation is 2.00.
     *
     * @param float $cgpa
     * @return bool
     */
    public function isEligibleForGraduation(float $cgpa): bool
    {
        return $cgpa >= 2.00;
    }
}
