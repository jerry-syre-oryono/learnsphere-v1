<?php

namespace Tests\Feature;

use App\Livewire\Student\CourseDisplay;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Course $course;
    private Lesson $completedLesson;
    private Lesson $uncompletedLesson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->student = User::factory()->create()->assignRole('student');
        $this->course = Course::factory()->create();
        $module = Module::factory()->for($this->course)->create();
        $this->completedLesson = Lesson::factory()->for($module)->for($this->course)->create(['title' => 'Completed Lesson']);
        $this->uncompletedLesson = Lesson::factory()->for($module)->for($this->course)->create(['title' => 'Uncompleted Lesson']);

        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $this->course->id]);
        $this->student->completedLessons()->attach($this->completedLesson->id);

        $this->actingAs($this->student);
    }

    public function test_course_display_page_renders_lessons_and_modules(): void
    {
        Livewire::test(CourseDisplay::class, ['course' => $this->course])
            ->assertSee($this->course->title)
            ->assertSee($this->course->modules->first()->title)
            ->assertSee($this->completedLesson->title)
            ->assertSee($this->uncompletedLesson->title);
    }

    public function test_completed_lessons_are_marked(): void
    {
        $response = Livewire::test(CourseDisplay::class, ['course' => $this->course]);
        
        // Assert that the completed lesson's SVG icon for completion is present
        $response->assertSeeHtml('<svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>');
        
        // Assert that the uncompleted lesson's SVG icon for uncompletion is present
        $response->assertSeeHtml('<svg class="w-6 h-6 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>');
    }
}
