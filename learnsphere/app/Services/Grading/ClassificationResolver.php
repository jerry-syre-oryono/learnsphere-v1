<?php

namespace App\Services\Grading;

/**
 * Resolves academic classification based on CGPA and program level.
 * Supports Diploma, Degree, and Certificate programs.
 *
 * NCHE Compliance: Classification follows Uganda NCHE standards.
 */
class ClassificationResolver
{
    private const DIPLOMA_CLASSIFICATIONS = [
        ['min_cgpa' => 4.00, 'classification' => 'Distinction', 'class' => null],
        ['min_cgpa' => 3.00, 'classification' => 'Credit', 'class' => null],
        ['min_cgpa' => 2.00, 'classification' => 'Pass', 'class' => null],
        ['min_cgpa' => 0, 'classification' => 'Fail', 'class' => null],
    ];

    private const DEGREE_CLASSIFICATIONS = [
        ['min_cgpa' => 4.40, 'classification' => null, 'class' => 'First Class'],
        ['min_cgpa' => 3.60, 'classification' => null, 'class' => 'Second Class Upper'],
        ['min_cgpa' => 2.80, 'classification' => null, 'class' => 'Second Class Lower'],
        ['min_cgpa' => 2.00, 'classification' => null, 'class' => 'Pass'],
        ['min_cgpa' => 0, 'classification' => null, 'class' => 'Fail'],
    ];

    /**
     * Resolve classification for a student based on CGPA and program level.
     *
     * @param float $cgpa The cumulative GPA
     * @param string $programLevel The program level (diploma, degree, certificate)
     * @return array ['classification' => string, 'class' => string|null, 'cgpa' => float]
     */
    public function resolve(float $cgpa, string $programLevel): array
    {
        $classifications = $this->getClassificationsForLevel($programLevel);

        foreach ($classifications as $range) {
            if ($cgpa >= $range['min_cgpa']) {
                return [
                    'classification' => $range['classification'] ?? $range['class'],
                    'class' => $range['class'],
                    'cgpa' => $cgpa,
                ];
            }
        }

        // For certificate programs, return null classification
        if (strtolower($programLevel) === 'certificate') {
            return [
                'classification' => null,
                'class' => null,
                'cgpa' => $cgpa,
            ];
        }

        // Fallback for degree/diploma
        return [
            'classification' => 'Fail',
            'class' => 'Fail',
            'cgpa' => $cgpa,
        ];
    }

    /**
     * Get classification ranges for a specific program level.
     *
     * @param string $programLevel
     * @return array
     */
    private function getClassificationsForLevel(string $programLevel): array
    {
        return match (strtolower($programLevel)) {
            'diploma' => self::DIPLOMA_CLASSIFICATIONS,
            'degree' => self::DEGREE_CLASSIFICATIONS,
            'certificate' => [], // Certificates may not use classification
            default => self::DEGREE_CLASSIFICATIONS,
        };
    }
}
