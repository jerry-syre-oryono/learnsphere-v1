<?php

namespace App\Services\Grading;

use App\Models\ProgramLevel;
use App\Models\GradingRule;

/**
 * Resolves grade boundaries based on program level and percentage marks.
 * Supports Diploma, Degree, and Certificate programs.
 *
 * NCHE Compliance: Grade boundaries follow Uganda NCHE standards.
 */
class GradeBoundaryResolver
{
    /**
     * Grade boundaries for Diploma/Degree (default)
     */
    private const DEFAULT_BOUNDARIES = [
        ['min' => 80, 'max' => 100, 'grade' => 'A', 'points' => 5.0],
        ['min' => 75, 'max' => 79, 'grade' => 'B+', 'points' => 4.5],
        ['min' => 70, 'max' => 74, 'grade' => 'B', 'points' => 4.0],
        ['min' => 65, 'max' => 69, 'grade' => 'C+', 'points' => 3.5],
        ['min' => 60, 'max' => 64, 'grade' => 'C', 'points' => 3.0],
        ['min' => 55, 'max' => 59, 'grade' => 'D+', 'points' => 2.5],
        ['min' => 50, 'max' => 54, 'grade' => 'D', 'points' => 2.0],
        ['min' => 0, 'max' => 49, 'grade' => 'F', 'points' => 0.0],
    ];

    /**
     * Resolve grade boundary for a given percentage mark.
     *
     * @param float $mark The percentage mark (0-100)
     * @param ProgramLevel $programLevel The program level (Diploma, Degree, Certificate)
     * @return array ['grade' => string, 'points' => float]
     */
    public function resolve(float $mark, ProgramLevel $programLevel): array
    {
        // Attempt to load custom grading rules from database
        $boundaries = $this->getBoundariesForProgramLevel($programLevel);

        foreach ($boundaries as $boundary) {
            if ($mark >= $boundary['min'] && $mark <= $boundary['max']) {
                return [
                    'grade' => $boundary['grade'],
                    'points' => $boundary['points'],
                ];
            }
        }

        // Fallback if mark is out of range
        return ['grade' => 'F', 'points' => 0.0];
    }

    /**
     * Get boundaries for a specific program level.
     *
     * @param ProgramLevel $programLevel
     * @return array
     */
    private function getBoundariesForProgramLevel(ProgramLevel $programLevel): array
    {
        // Try to load from database first
        $customRules = GradingRule::where('program_level_id', $programLevel->id)
            ->orderBy('min_percentage', 'desc')
            ->get();

        if ($customRules->isNotEmpty()) {
            return $customRules->map(fn ($rule) => [
                'min' => $rule->min_percentage,
                'max' => $rule->max_percentage,
                'grade' => $rule->letter_grade,
                'points' => $rule->grade_point,
            ])->toArray();
        }

        // Use default boundaries
        return self::DEFAULT_BOUNDARIES;
    }
}
