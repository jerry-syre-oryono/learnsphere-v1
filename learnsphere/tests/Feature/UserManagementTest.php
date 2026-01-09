<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_access_user_management()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.user-management.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_instructor_can_access_user_management()
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $response = $this->actingAs($instructor)
            ->get(route('admin.user-management.index'));

        $response->assertStatus(200);
    }

    public function test_student_cannot_access_user_management()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($student)
            ->get(route('admin.user-management.index'));

        $response->assertStatus(403);
    }

    public function test_student_can_access_profile()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($student)
            ->get(route('student.profile'));

        $response->assertStatus(200);
        $response->assertSee('My Academic Performance');
    }

    public function test_admin_cannot_access_student_profile()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('student.profile'));

        $response->assertStatus(403);
    }
}