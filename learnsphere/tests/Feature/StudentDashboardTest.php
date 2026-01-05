<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Course $enrolledCourse;
    private Course $catalogCourse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->student = User::factory()->create()->assignRole('student');

        $this->enrolledCourse = Course::factory()->create(['title' => 'Enrolled Course']);
        $this->catalogCourse = Course::factory()->create(['title' => 'Catalog Course']);

        Enrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $this->enrolledCourse->id,
        ]);

        $this->student->refresh();

        $this->actingAs($this->student);
    }

    public function test_dashboard_renders_correctly_and_shows_courses(): void
    {
        Livewire::test(Dashboard::class)
            ->assertSee('My Courses')
            ->assertSee('Enrolled Course')
            ->assertSee('Course Catalog')
            ->assertSee('Catalog Course')
            ->assertDontSee('No other courses available');
    }

    public function test_user_can_enroll_in_a_course(): void
    {
        Livewire::test(Dashboard::class)
            ->assertSee('Catalog Course')
            ->call('enroll', $this->catalogCourse->id)
            ->assertDispatched('enrolled')
            ->assertSee('Continue Learning') // The enrolled course card appears
            ->assertDontSee('Enroll Now'); // The button in the catalog is gone

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->catalogCourse->id,
        ]);
    }
}
