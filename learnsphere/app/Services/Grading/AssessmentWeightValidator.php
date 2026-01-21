<?php

namespace App\Services\Grading;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Support\Collection;

/**
 * Assessment Weight Validator
 *
 * Validates assessment weights for a course:
 * - All weights must be 0-100
 * - Weights should sum to 100 (or configurable)
 * - No negative weights
 * - All assessments have weight defined
 *
 * NCHE Compliance: Ensures proper grade composition
 */
class AssessmentWeightValidator
{
    /**
     * Validate weights for all assessments in a course.
     *
     * @param Course $course
     * @param float $expectedTotal Default 100 (can be overridden)
     * @return array ['valid' => bool, 'errors' => [], 'warnings' => []]
     */
    public function validate(Course $course, float $expectedTotal = 100.0): array
    {
        $assessableItems = $course->getAssessableItemsAttribute() ?? collect();
        $errors = [];
        $warnings = [];
        $totalWeight = 0;
        $itemsWithWeight = 0;

        // Validate individual weights
        foreach ($assessableItems as $item) {
            $weight = $item->weight ?? null;
            $itemName = $this->getItemName($item);

            // Weight not defined
            if ($weight === null || $weight === '') {
                $warnings[] = "Assessment '{$itemName}' has no weight defined";
                continue;
            }

            // Weight not numeric
            if (!is_numeric($weight)) {
                $errors[] = "Assessment '{$itemName}' weight is not numeric: {$weight}";
                continue;
            }

            // Weight negative
            if ($weight < 0) {
                $errors[] = "Assessment '{$itemName}' has negative weight: {$weight}";
                continue;
            }

            // Weight > 100 (likely error)
            if ($weight > 100) {
                $errors[] = "Assessment '{$itemName}' weight exceeds 100: {$weight}";
                continue;
            }

            // Weight = 0 (no contribution)
            if ($weight == 0) {
                $warnings[] = "Assessment '{$itemName}' has zero weight (will not contribute to grade)";
            }

            $totalWeight += (float)$weight;
            $itemsWithWeight++;
        }

        // Validate total weight
        if ($totalWeight !== $expectedTotal && $itemsWithWeight > 0) {
            $errors[] = "Total weight ({$totalWeight}) does not equal expected total ({$expectedTotal})";
        }

        // No assessments with weight
        if ($itemsWithWeight === 0) {
            $errors[] = "No assessments with valid weights defined for course";
        }

        return [
            'valid' => empty($errors),
            'total_weight' => $totalWeight,
            'items_with_weight' => $itemsWithWeight,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get a human-readable name for an assessment/assignment.
     *
     * @param Assessment|Assignment $item
     * @return string
     */
    private function getItemName($item): string
    {
        if ($item instanceof Assessment) {
            return $item->title ?? "Assessment {$item->id}";
        }
        if ($item instanceof Assignment) {
            return $item->title ?? "Assignment {$item->id}";
        }
        return "Unknown item {$item->id}";
    }

    /**
     * Check if weights are normalized (0-1 instead of 0-100).
     * Used for validation before calculation.
     *
     * @param Collection $assessableItems
     * @return bool
     */
    public function areNormalized(Collection $assessableItems): bool
    {
        $maxWeight = $assessableItems->max('weight') ?? 0;
        return $maxWeight <= 1.0;
    }

    /**
     * Convert weights from 0-100 to 0-1 (normalized).
     *
     * @param Collection $assessableItems
     * @return Collection
     */
    public function normalize(Collection $assessableItems): Collection
    {
        return $assessableItems->map(function ($item) {
            if ($item->weight && $item->weight > 1) {
                $item->weight = $item->weight / 100;
            }
            return $item;
        });
    }

    /**
     * Assert weights are valid for calculation.
     * Throws exception if invalid.
     *
     * @param Course $course
     * @param float $expectedTotal
     * @throws \InvalidArgumentException
     * @return void
     */
    public function assert(Course $course, float $expectedTotal = 100.0): void
    {
        $result = $this->validate($course, $expectedTotal);

        if (!$result['valid']) {
            $errorList = implode('; ', $result['errors']);
            throw new \InvalidArgumentException(
                "Assessment weight validation failed: {$errorList}"
            );
        }
    }
}
