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
        $this->lesson = $lesson->load('quiz'); // Eager load the quiz if it exists
        $this->isCompleted = Auth::user()->completedLessons->contains($lesson->id);
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
