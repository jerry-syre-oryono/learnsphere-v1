<?php

namespace App\Services\Grading;

use App\Models\ProgramLevel;

/**
 * Enforces retake grade cap policy.
 *
 * NCHE Policy: "In accordance with institutional and NCHE regulations,
 * the maximum grade attainable in a repeated course shall not exceed a Credit (C)."
 *
 * Maximum capped grade: C (3.0 grade points)
 */
class RetakeCapEnforcer
{
    private const CAP_GRADE = 'C';
    private const CAP_POINTS = 3.0;

    /**
     * Enforce retake cap on grade point.
     *
     * @param string $letterGrade The original letter grade
     * @param float $gradePoint The original grade point
     * @param float $creditUnits Credit units
     * @return array [
     *     'letter_grade' => string,
     *     'grade_point' => float,
     *     'grade_points_earned' => float,
     *     'was_capped' => bool
     * ]
     */
    public function enforce(string $letterGrade, float $gradePoint, float $creditUnits = 3.0): array
    {
        $wasCapped = false;

        // Cap at C (3.0)
        if ($gradePoint > self::CAP_POINTS) {
            $gradePoint = self::CAP_POINTS;
            $letterGrade = self::CAP_GRADE;
            $wasCapped = true;
        }

        return [
            'letter_grade' => $letterGrade,
            'grade_point' => $gradePoint,
            'grade_points_earned' => $gradePoint * $creditUnits,
            'was_capped' => $wasCapped,
        ];
    }
}
