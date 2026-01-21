<?php

namespace App\Livewire\Student;

use App\Models\StudentCourseResult;
use Livewire\Component;

/**
 * Course Grade Card Component
 *
 * Displays individual course grade information.
 * Shows letter grade, grade point, retake status, and caps.
 */
class CourseGradeCard extends Component
{
    public StudentCourseResult $result;
    public bool $showDetails = false;

    public function toggleDetails()
    {
        $this->showDetails = !$this->showDetails;
    }

    public function render()
    {
        return view('livewire.student.course-grade-card', [
            'result' => $this->result,
            'showDetails' => $this->showDetails,
        ]);
    }
}
