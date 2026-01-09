<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Services\ProgressService;
use App\Services\StudentNumberService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseDisplay extends Component
{
    public Course $course;
    public $modules;
    public array $completedLessonIds = [];
    public float $progress = 0;

    protected ProgressService $progressService;
    protected StudentNumberService $studentNumberService;

    public function boot(ProgressService $progressService, StudentNumberService $studentNumberService): void
    {
        $this->progressService = $progressService;
        $this->studentNumberService = $studentNumberService;
    }

    public function mount(Course $course): void
    {
        $this->course = $course->load(['modules.lessons.quiz', 'modules.assignments']); // Eager load modules, lessons, quizzes, and assignments
        $this->modules = $this->course->modules;

        $user = Auth::user();
        $this->completedLessonIds = $user
            ->completedLessons()
            ->whereIn('lessons.course_id', [$course->id])
            ->pluck('lessons.id')
            ->toArray();

        $this->isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();

        if ($this->isEnrolled) {
            $this->progress = $this->progressService->getOverallCourseProgress($user, $course);
        }
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

        // Perform the enrollment
        Auth::user()->enrolledCourses()->attach($this->course->id);
        $this->isEnrolled = true;

        // Generate student number AFTER successful enrollment
        // This ensures student numbers are only created when enrollment actually succeeds
        try {
            $this->studentNumberService->generateStudentNumber(Auth::user(), $this->course);
        } catch (\Exception $e) {
            // Log the error but don't fail the enrollment process
            // Student number generation failure shouldn't prevent course access
            \Log::error('Failed to generate student number for user ' . Auth::user()->id . ' in course ' . $this->course->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

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
