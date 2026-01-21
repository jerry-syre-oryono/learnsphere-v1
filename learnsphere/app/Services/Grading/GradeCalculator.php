<?php

namespace App\Services\Grading;

use App\Models\ProgramLevel;

/**
 * Calculates grade and grade points for a course based on percentage marks.
 * Enforces NCHE regulations and handles edge cases.
 */
class GradeCalculator
{
    public function __construct(
        private GradeBoundaryResolver $boundaryResolver,
        private RetakeCapEnforcer $retakeCapEnforcer,
    ) {}

    /**
     * Calculate grade and grade point for a student in a course.
     *
     * @param float $percentageMark The percentage mark (0-100)
     * @param ProgramLevel $programLevel The program level
     * @param float $creditUnits Credit units for the course
     * @param bool $isRetake Whether this is a retaken course
     * @return array [
     *     'letter_grade' => string,
     *     'grade_point' => float,
     *     'grade_points_earned' => float,
     *     'was_capped' => bool,
     *     'original_grade' => string|null,
     *     'capped_grade' => string|null
     * ]
     */
    public function calculate(
        float $percentageMark,
        ProgramLevel $programLevel,
        float $creditUnits = 3.0,
        bool $isRetake = false
    ): array {
        // Ensure mark is within valid range
        $percentageMark = max(0, min(100, $percentageMark));

        // Get initial grade boundary
        $boundary = $this->boundaryResolver->resolve($percentageMark, $programLevel);
        $letterGrade = $boundary['grade'];
        $gradePoint = $boundary['points'];

        $result = [
            'letter_grade' => $letterGrade,
            'grade_point' => $gradePoint,
            'grade_points_earned' => $gradePoint * $creditUnits,
            'was_capped' => false,
            'original_grade' => null,
            'capped_grade' => null,
        ];

        // If retaken, apply cap enforcement
        if ($isRetake) {
            $cappedResult = $this->retakeCapEnforcer->enforce($letterGrade, $gradePoint, $creditUnits);

            $result['letter_grade'] = $cappedResult['letter_grade'];
            $result['grade_point'] = $cappedResult['grade_point'];
            $result['grade_points_earned'] = $cappedResult['grade_points_earned'];
            $result['was_capped'] = $cappedResult['was_capped'];
            $result['original_grade'] = $letterGrade;
            $result['capped_grade'] = $cappedResult['letter_grade'];
        }

        return $result;
    }
}
