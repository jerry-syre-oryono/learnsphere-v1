<?php

namespace App\Services\Grading;

use App\Models\Enrollment;
use App\Models\StudentCourseResult;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Main Grading Service Orchestrator
 *
 * Coordinates all grading operations in a single, unified interface.
 * This is the primary service that controllers should use.
 */
class GradingService
{
    public function __construct(
        private GradeCalculator $gradeCalculator,
        private GPACalculator $gpaCalculator,
        private CGPACalculator $cgpaCalculator,
        private ClassificationResolver $classificationResolver,
        private AcademicStandingResolver $academicStandingResolver,
        private GradeBoundaryResolver $boundaryResolver,
    ) {}

    /**
     * Process and store a student's course grade.
     *
     * @param Enrollment $enrollment
     * @param float $percentageMark
     * @param float $creditUnits
     * @param bool $isRetake
     * @param string|null $semester
     * @return StudentCourseResult
     */
    public function processStudentGrade(
        Enrollment $enrollment,
        float $percentageMark,
        float $creditUnits = 3.0,
        bool $isRetake = false,
        ?string $semester = null
    ): StudentCourseResult {
        // Calculate grade
        $gradeResult = $this->gradeCalculator->calculate(
            $percentageMark,
            $enrollment->programLevel ?? $enrollment->course->programLevel,
            $creditUnits,
            $isRetake
        );

        // Store result
        $courseResult = StudentCourseResult::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'semester' => $semester ?? $this->getCurrentSemester(),
            ],
            [
                'final_mark' => $percentageMark,
                'letter_grade' => $gradeResult['letter_grade'],
                'grade_point' => $gradeResult['grade_point'],
                'grade_points_earned' => $gradeResult['grade_points_earned'],
                'credit_units' => $creditUnits,
                'is_retake' => $isRetake,
                'was_capped' => $gradeResult['was_capped'],
                'original_grade' => $gradeResult['original_grade'],
                'capped_grade' => $gradeResult['capped_grade'],
                'calculated_at' => Carbon::now(),
            ]
        );

        return $courseResult;
    }

    /**
     * Calculate semester GPA for a student.
     *
     * @param Enrollment $enrollment
     * @param string|null $semester
     * @return float
     */
    public function calculateSemesterGPA(Enrollment $enrollment, ?string $semester = null): float
    {
        return $this->gpaCalculator->calculateSemesterGPA($enrollment, $semester);
    }

    /**
     * Calculate cumulative GPA for a student.
     *
     * @param User $student
     * @return float
     */
    public function calculateCGPA(User $student): float
    {
        return $this->cgpaCalculator->calculateForStudent($student);
    }

    /**
     * Get academic classification for a student.
     *
     * @param User $student
     * @return array
     */
    public function getAcademicClassification(User $student): array
    {
        $cgpa = $this->calculateCGPA($student);
        $programLevel = $this->getStudentProgramLevel($student);

        return $this->classificationResolver->resolve($cgpa, strtolower($programLevel));
    }

    /**
     * Get academic standing for a student.
     *
     * @param User $student
     * @return array
     */
    public function getAcademicStanding(User $student): array
    {
        $cgpa = $this->calculateCGPA($student);

        return $this->academicStandingResolver->resolve($student, $cgpa);
    }

    /**
     * Get complete grade report for a student.
     *
     * @param User $student
     * @return array
     */
    public function getCompleteGradeReport(User $student): array
    {
        $cgpa = $this->calculateCGPA($student);
        $classification = $this->getAcademicClassification($student);
        $standing = $this->getAcademicStanding($student);

        return [
            'student' => $student,
            'cgpa' => $cgpa,
            'classification' => $classification,
            'standing' => $standing,
            'is_eligible_for_graduation' => $this->cgpaCalculator->isEligibleForGraduation($cgpa),
            'can_continue_studies' => $this->academicStandingResolver->isEligibleToContinue($cgpa),
        ];
    }

    /**
     * Get grade report for a specific enrollment.
     *
     * @param Enrollment $enrollment
     * @param string|null $semester
     * @return array
     */
    public function getEnrollmentGradeReport(Enrollment $enrollment, ?string $semester = null): array
    {
        $gpa = $this->calculateSemesterGPA($enrollment, $semester);
        $courseResults = StudentCourseResult::where('enrollment_id', $enrollment->id)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->get();

        return [
            'enrollment' => $enrollment,
            'semester' => $semester ?? $this->getCurrentSemester(),
            'gpa' => $gpa,
            'course_results' => $courseResults,
            'total_credit_units' => $courseResults->sum('credit_units'),
            'total_grade_points' => $courseResults->sum('grade_points_earned'),
        ];
    }

    /**
     * Get the current academic semester.
     *
     * @return string
     */
    private function getCurrentSemester(): string
    {
        $year = date('Y');
        $month = date('m');

        // Assume: Jan-Jun = Semester 1, Jul-Dec = Semester 2
        $semester = $month <= 6 ? 1 : 2;

        return "{$year}-" . ($year + 1) . "-{$semester}";
    }

    /**
     * Get the program level for a student.
     *
     * @param User $student
     * @return string
     */
    private function getStudentProgramLevel(User $student): string
    {
        $enrollment = $student->enrollments()->with('programLevel')->first();

        if ($enrollment?->programLevel) {
            return $enrollment->programLevel->name;
        }

        return 'degree'; // Default
    }
}
