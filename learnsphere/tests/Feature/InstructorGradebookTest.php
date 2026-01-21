<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorGradebookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles if they don't exist
        if (!\Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'admin']);
            \Spatie\Permission\Models\Role::create(['name' => 'instructor']);
            \Spatie\Permission\Models\Role::create(['name' => 'student']);
        }
    }

    public function test_instructor_can_access_gradebook()
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $response = $this->actingAs($instructor)
            ->get(route('admin.gradebook'));

        $response->assertStatus(200);
        $response->assertSee('Gradebook');
    }

    public function test_instructor_only_sees_their_own_courses_in_gradebook()
    {
        $instructor1 = User::factory()->create();
        $instructor1->assignRole('instructor');

        $instructor2 = User::factory()->create();
        $instructor2->assignRole('instructor');

        // Create courses for each instructor
        $course1 = Course::factory()->create(['instructor_id' => $instructor1->id, 'title' => 'Course 1']);
        $course2 = Course::factory()->create(['instructor_id' => $instructor2->id, 'title' => 'Course 2']);

        // Instructor 1 should only see their course
        $response = $this->actingAs($instructor1)
            ->get(route('admin.gradebook'));

        $response->assertStatus(200);
        $response->assertSee('Course 1');
        $response->assertDontSee('Course 2');

        // Instructor 2 should only see their course
        $response = $this->actingAs($instructor2)
            ->get(route('admin.gradebook'));

        $response->assertStatus(200);
        $response->assertSee('Course 2');
        $response->assertDontSee('Course 1');
    }

    public function test_admin_sees_all_courses_in_gradebook()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        // Create courses for instructor and admin
        $course1 = Course::factory()->create(['instructor_id' => $instructor->id, 'title' => 'Instructor Course']);
        $course2 = Course::factory()->create(['instructor_id' => $admin->id, 'title' => 'Admin Course']);

        $response = $this->actingAs($admin)
            ->get(route('admin.gradebook'));

        $response->assertStatus(200);
        $response->assertSee('Instructor Course');
        $response->assertSee('Admin Course');
    }

    public function test_instructor_can_access_course_specific_gradebook_for_their_course()
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $response = $this->actingAs($instructor)
            ->get(route('gradebook.index', $course));

        $response->assertStatus(200);
    }

    public function test_instructor_cannot_access_course_specific_gradebook_for_other_instructor_course()
    {
        $instructor1 = User::factory()->create();
        $instructor1->assignRole('instructor');

        $instructor2 = User::factory()->create();
        $instructor2->assignRole('instructor');

        $course = Course::factory()->create(['instructor_id' => $instructor2->id]);

        $response = $this->actingAs($instructor1)
            ->get(route('gradebook.index', $course));

        $response->assertStatus(403);
    }
}
