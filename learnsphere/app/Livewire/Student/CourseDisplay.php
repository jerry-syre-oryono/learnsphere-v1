<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Services\ProgressService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseDisplay extends Component
{
    public Course $course;
    public $modules;
    public array $completedLessonIds = [];

    protected ProgressService $progressService;

    public function boot(ProgressService $progressService): void
    {
        $this->progressService = $progressService;
    }

    public function mount(Course $course): void
    {
        $this->course = $course->load(['modules.lessons.quiz']); // Eager load modules, lessons, and quizzes
        $this->modules = $this->course->modules;

        $this->completedLessonIds = Auth::user()
            ->completedLessons()
            ->whereIn('lessons.course_id', [$course->id])
            ->pluck('lessons.id')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.student.course-display');
    }
}
