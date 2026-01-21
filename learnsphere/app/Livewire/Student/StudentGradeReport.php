<?php

namespace App\Livewire\Student;

use App\Models\User;
use App\Services\Grading\GradingService;
use Livewire\Component;

/**
 * Student Grade Report Component
 *
 * Displays student's grade information, GPA, CGPA, and academic standing.
 * Read-only component.
 */
class StudentGradeReport extends Component
{
    public User $student;
    public array $gradeReport = [];
    public array $semesters = [];
    public string $selectedSemester = '';

    public function mount(User $student, GradingService $gradingService)
    {
        $this->student = $student;
        $this->gradeReport = $gradingService->getCompleteGradeReport($student);

        // Get all available semesters
        $this->semesters = \App\Models\StudentCourseResult::where('enrollment_id', $student->enrollments->pluck('id'))
            ->distinct()
            ->pluck('semester')
            ->sort()
            ->values()
            ->toArray();

        $this->selectedSemester = end($this->semesters) ?: '';
    }

    public function render()
    {
        return view('livewire.student.grade-report', [
            'gradeReport' => $this->gradeReport,
        ]);
    }
}
