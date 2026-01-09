<?php

namespace App\Livewire\Student;



use App\Models\Course;

use App\Models\Enrollment;

use App\Services\ProgressService;
use App\Services\StudentNumberService;

use Illuminate\Support\Facades\Auth;

use Livewire\Component;



class Dashboard extends Component

{

    public $enrolledCourses;

    public $courseCatalog;



    protected ProgressService $progressService;
    protected StudentNumberService $studentNumberService;



    public function boot(ProgressService $progressService, StudentNumberService $studentNumberService): void

    {

        $this->progressService = $progressService;
        $this->studentNumberService = $studentNumberService;

    }



    public function mount(): void

    {

        $this->loadCourses();

    }



    public function loadCourses(): void

    {

        $user = Auth::user();



        // Load only published enrolled courses

        $this->enrolledCourses = $user->enrolledCourses()

            ->published()

            ->get()

            ->map(function ($course) use ($user) {

                $course->completion_percentage = $this->progressService->getCourseCompletionPercentage($user, $course);

                return $course;

            });



        $enrolledCourseIds = $this->enrolledCourses->pluck('id');



        // Load only published courses not already enrolled

        $this->courseCatalog = Course::published()

            ->whereNotIn('id', $enrolledCourseIds)

            ->get();

    }



    public function enroll(Course $course): void

    {

        // Use syncWithoutDetaching to prevent duplicate enrollments

        Auth::user()->enrolledCourses()->syncWithoutDetaching($course->id);

        // Generate student number AFTER successful enrollment
        // This ensures student numbers are only created when enrollment actually succeeds
        try {
            $this->studentNumberService->generateStudentNumber(Auth::user(), $course);
        } catch (\Exception $e) {
            // Log the error but don't fail the enrollment process
            // Student number generation failure shouldn't prevent course access
            \Log::error('Failed to generate student number for user ' . Auth::user()->id . ' in course ' . $course->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->loadCourses();



        $this->dispatch('enrolled', courseTitle: $course->title);

    }



    public function render()

    {

        return view('livewire.student.dashboard');

    }

}
