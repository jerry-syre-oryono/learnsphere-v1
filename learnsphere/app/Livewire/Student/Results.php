<?php

namespace App\Livewire\Student;

use App\Services\Grading\GradingService;
use App\Services\FinalGradeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Results extends Component
{
    public array $courseData = [];

    public function mount(GradingService $gradingService, FinalGradeService $finalGradeService)
    {
        $user = Auth::user();
        $enrollments = $user->enrollments()->with('course.instructor')->get();

        foreach ($enrollments as $enrollment) {
            $gradeReport = $gradingService->getEnrollmentGradeReport($enrollment);
            
            // Get all assessable items for the course
            $assessableItems = $enrollment->course->getAssessableItemsAttribute();
            
            // Get all submissions for the student for that course
            $submissions = $user->submissions()
                ->whereIn('submittable_id', $assessableItems->pluck('id'))
                ->whereIn('submittable_type', $assessableItems->map(fn($item) => get_class($item))->unique())
                ->get()
                ->keyBy('submittable_id');

            $this->courseData[] = [
                'report' => $gradeReport,
                'assessableItems' => $assessableItems,
                'submissions' => $submissions,
            ];
        }
    }

    public function render()
    {
        return view('livewire.student.results');
    }
}
