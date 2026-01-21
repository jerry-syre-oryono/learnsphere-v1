<?php

namespace App\Livewire\Student;

use App\Models\Enrollment;
use App\Services\Grading\GradingService;
use Livewire\Component;

/**
 * Grade Summary Component
 *
 * Displays GPA, CGPA, and academic classification.
 * Read-only component with informational badges.
 */
class GradeSummary extends Component
{
    public Enrollment $enrollment;
    public float $gpa = 0;
    public float $cgpa = 0;
    public array $classification = [];
    public array $standing = [];

    public function mount(Enrollment $enrollment, GradingService $gradingService)
    {
        $this->enrollment = $enrollment;
        $this->gpa = $gradingService->calculateSemesterGPA($enrollment);

        $student = $enrollment->user;
        $this->cgpa = $gradingService->calculateCGPA($student);
        $this->classification = $gradingService->getAcademicClassification($student);
        $this->standing = $gradingService->getAcademicStanding($student);
    }

    public function render()
    {
        return view('livewire.student.grade-summary', [
            'gpa' => $this->gpa,
            'cgpa' => $this->cgpa,
            'classification' => $this->classification,
            'standing' => $this->standing,
        ]);
    }
}
