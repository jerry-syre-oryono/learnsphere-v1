<?php

namespace App\Livewire\Student;

use App\Models\Lesson;
use App\Services\ProgressService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LessonView extends Component
{
    public Lesson $lesson;
    public bool $isCompleted;
    public $quiz;

    protected ProgressService $progressService;

    public function boot(ProgressService $progressService): void
    {
        $this->progressService = $progressService;
    }

    public function mount(Lesson $lesson): void
    {
        $user = Auth::user();
        $course = $lesson->course;

        // Security check: must be enrolled, instructor, or admin
        $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
        $isInstructor = $course->instructor_id === $user->id;
        $isAdmin = $user->hasRole('admin');

        if (!$isEnrolled && !$isInstructor && !$isAdmin) {
            abort(403, 'You must be enrolled to view this lesson.');
        }

        $this->lesson = $lesson->load('quiz'); // Eager load the quiz if it exists
        $this->isCompleted = $user->completedLessons->contains($lesson->id);
        $this->quiz = $lesson->quiz;
    }

    public function markAsComplete(): void
    {
        $this->progressService->markLessonAsComplete(Auth::user(), $this->lesson);
        $this->isCompleted = true; // Update local state
        $this->dispatch('lessonCompleted', lessonId: $this->lesson->id);
    }

    public function render()
    {
        return view('livewire.student.lesson-view');
    }
}
