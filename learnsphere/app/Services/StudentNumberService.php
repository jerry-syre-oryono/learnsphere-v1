<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service for generating and managing student numbers.
 *
 * Student numbers follow the format: COURSE_CODE-S-YEAR-SEQUENCE
 * - COURSE_CODE: Derived from course title (e.g., "Diploma in VFX" → "DVFX")
 * - YEAR: Enrollment year (server-side, not client input)
 * - SEQUENCE: Zero-padded incremental number scoped per COURSE + YEAR
 *
 * Generation happens ONLY during course enrollment, not during registration or approval flows.
 */
class StudentNumberService
{
    /**
     * Generate a student number for the given user and course.
     *
     * This method is atomic and safe under concurrent enrollments.
     * If the student is already enrolled in the same course for the same year,
     * it returns the existing student number.
     *
     * @param User $user The student user
     * @param Course $course The course being enrolled in
     * @return string The generated student number
     * @throws \Exception If student number generation fails
     */
    public function generateStudentNumber(User $user, Course $course): string
    {
        // Check if student already has a number for this course in the current year
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('enrollment_year', now()->year)
            ->whereNotNull('student_number')
            ->first();

        if ($existingEnrollment) {
            return $existingEnrollment->student_number;
        }

        return DB::transaction(function () use ($user, $course) {
            // Lock the enrollments table to prevent concurrent sequence generation
            $nextSequence = $this->getNextSequenceNumber($course, now()->year);

            $studentNumber = $this->formatStudentNumber(
                $course->getCourseCode(),
                now()->year,
                $nextSequence
            );

            // Update the enrollment record with the student number and year
            Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereNull('student_number') // Only update if not already set
                ->update([
                    'student_number' => $studentNumber,
                    'enrollment_year' => now()->year,
                ]);

            return $studentNumber;
        });
    }

    /**
     * Get the next sequence number for a course and year combination.
     *
     * Uses database locking to ensure atomicity under concurrent enrollments.
     *
     * @param Course $course
     * @param int $year
     * @return int
     */
    private function getNextSequenceNumber(Course $course, int $year): int
    {
        // Find the highest existing sequence number for this course and year
        $maxSequence = Enrollment::where('course_id', $course->id)
            ->where('enrollment_year', $year)
            ->whereNotNull('student_number')
            ->lockForUpdate() // Prevent concurrent access
            ->get()
            ->map(function ($enrollment) {
                // Extract sequence number from student_number (e.g., "DVFX-S-2026-001" → 1)
                $parts = explode('-', $enrollment->student_number);
                return isset($parts[3]) ? (int) $parts[3] : 0;
            })
            ->max();

        return $maxSequence ? $maxSequence + 1 : 1;
    }

    /**
     * Format a student number according to the specification.
     *
     * @param string $courseCode
     * @param int $year
     * @param int $sequence
     * @return string
     */
    private function formatStudentNumber(string $courseCode, int $year, int $sequence): string
    {
        return sprintf('%s-S-%d-%03d', $courseCode, $year, $sequence);
    }

    /**
     * Get student number for an enrollment.
     *
     * @param Enrollment $enrollment
     * @return string|null
     */
    public function getStudentNumber(Enrollment $enrollment): ?string
    {
        return $enrollment->student_number;
    }

    /**
     * Check if a student number already exists for a given course and year.
     *
     * @param string $studentNumber
     * @param Course $course
     * @param int $year
     * @return bool
     */
    public function studentNumberExists(string $studentNumber, Course $course, int $year): bool
    {
        return Enrollment::where('student_number', $studentNumber)
            ->where('course_id', $course->id)
            ->where('enrollment_year', $year)
            ->exists();
    }
}