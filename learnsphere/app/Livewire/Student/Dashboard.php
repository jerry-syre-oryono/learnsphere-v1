<?php

namespace App\Livewire\Student;



use App\Models\Course;

use App\Models\Enrollment;

use App\Services\ProgressService;

use Illuminate\Support\Facades\Auth;

use Livewire\Component;



class Dashboard extends Component

{

    public $enrolledCourses;

    public $courseCatalog;



    protected ProgressService $progressService;



    public function boot(ProgressService $progressService): void

    {

        $this->progressService = $progressService;

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



        $this->loadCourses();



        $this->dispatch('enrolled', courseTitle: $course->title);

    }



    public function render()

    {

        return view('livewire.student.dashboard');

    }

}
