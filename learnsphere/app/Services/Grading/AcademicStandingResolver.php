<?php

namespace App\Services\Grading;

use App\Models\User;

/**
 * Determines academic standing for a student.
 *
 * NCHE Compliance: Academic standing rules follow institutional and NCHE regulations.
 *
 * Standing Types:
 * - Normal Progress: CGPA ≥ 2.00
 * - Academic Probation: CGPA < 2.00
 * - Discontinuation: Same core course failed twice OR probation for consecutive semesters
 */
class AcademicStandingResolver
{
    private const STANDING_NORMAL = 'normal';
    private const STANDING_PROBATION = 'probation';
    private const STANDING_DISCONTINUED = 'discontinued';

    /**
     * Determine academic standing for a student.
     *
     * @param User $student
     * @param float $cgpa Current CGPA
     * @return array [
     *     'standing' => string,
     *     'status' => string,
     *     'message' => string,
     *     'on_probation' => bool,
     *     'cgpa' => float
     * ]
     */
    public function resolve(User $student, float $cgpa): array
    {
        // Check if student has been discontinued
        if ($this->isDiscontinued($student)) {
            return [
                'standing' => self::STANDING_DISCONTINUED,
                'status' => 'Discontinued',
                'message' => 'Student has been discontinued from the program.',
                'on_probation' => false,
                'cgpa' => $cgpa,
            ];
        }

        // Check if on probation
        if ($cgpa < 2.00) {
            return [
                'standing' => self::STANDING_PROBATION,
                'status' => 'Academic Probation',
                'message' => 'Student is on academic probation. CGPA must be improved to 2.00 or above.',
                'on_probation' => true,
                'cgpa' => $cgpa,
            ];
        }

        // Normal standing
        return [
            'standing' => self::STANDING_NORMAL,
            'status' => 'Good Standing',
            'message' => 'Student is in good academic standing.',
            'on_probation' => false,
            'cgpa' => $cgpa,
        ];
    }

    /**
     * Check if a student has been discontinued.
     *
     * Discontinuation occurs when:
     * - Same core course failed twice, OR
     * - On probation for consecutive semesters
     *
     * @param User $student
     * @return bool
     */
    private function isDiscontinued(User $student): bool
    {
        // For now, this checks for a discontinuation flag
        // In production, would check historical probation records and failed courses
        return $student->is_discontinued ?? false;
    }

    /**
     * Check if student is eligible to continue in next semester.
     *
     * @param float $cgpa
     * @return bool
     */
    public function isEligibleToContinue(float $cgpa): bool
    {
        return $cgpa >= 2.00;
    }
}
