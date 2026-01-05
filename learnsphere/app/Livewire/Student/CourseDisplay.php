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
        $this->course = $course->load(['modules.lessons.quiz', 'modules.assignments']); // Eager load modules, lessons, quizzes, and assignments
        $this->modules = $this->course->modules;

        $this->completedLessonIds = Auth::user()
            ->completedLessons()
            ->whereIn('lessons.course_id', [$course->id])
            ->pluck('lessons.id')
            ->toArray();

        $this->isEnrolled = Auth::user()->enrolledCourses()->where('course_id', $course->id)->exists();
    }

    public bool $isEnrolled = false;
    public string $enrollmentInput = '';

    public function enroll()
    {
        if (!Auth::user()->hasRole('student')) {
            return;
        }

        if ($this->isEnrolled) {
            return;
        }

        Auth::user()->enrolledCourses()->attach($this->course->id);
        $this->isEnrolled = true;

        $this->dispatch('course-enrolled');
    }

    /**
     * Remove a student from the course (Instructor/Admin only)
     */
    public function removeStudent($userId)
    {
        $course = $this->course;
        $user = Auth::user();

        // Security check
        if ($course->instructor_id !== $user->id && !$user->hasRole('admin')) {
            abort(403);
        }

        \App\Models\Enrollment::where('course_id', $course->id)
            ->where('user_id', $userId)
            ->delete();

        $this->course->load('students'); // Refresh the students collection
        $this->mount($this->course);
        $this->dispatch('student-removed');
    }

    public function render()
    {
        return view('livewire.student.course-display');
    }
}
