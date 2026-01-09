<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_enrollment_creates_student_number()
    {
        // Create a course and user
        $course = Course::factory()->create(['title' => 'Test Course']);
        $user = User::factory()->create();

        // Simulate enrollment via dashboard (syncWithoutDetaching)
        $user->enrolledCourses()->syncWithoutDetaching($course->id);

        // Manually trigger student number generation (simulating what Dashboard does)
        $studentNumberService = app(\App\Services\StudentNumberService::class);
        $studentNumber = $studentNumberService->generateStudentNumber($user, $course);

        // Verify enrollment exists and has student number
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertEquals($studentNumber, $enrollment->student_number);
        $this->assertNotNull($enrollment->enrollment_year);
        $this->assertEquals(now()->year, $enrollment->enrollment_year);

        // Verify format
        $this->assertMatchesRegularExpression('/^TC-S-\d{4}-\d{3}$/', $enrollment->student_number);
    }

    public function test_both_enrollment_methods_work()
    {
        $course = Course::factory()->create(['title' => 'Test Course']);
        $studentNumberService = app(\App\Services\StudentNumberService::class);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 enrolls via CourseDisplay (attach)
        Enrollment::create(['user_id' => $user1->id, 'course_id' => $course->id]);
        $number1 = $studentNumberService->generateStudentNumber($user1, $course);

        // User 2 enrolls via Dashboard (syncWithoutDetaching)
        $user2->enrolledCourses()->syncWithoutDetaching($course->id);
        $number2 = $studentNumberService->generateStudentNumber($user2, $course);

        // Both should get sequential numbers
        $seq1 = (int) substr($number1, -3);
        $seq2 = (int) substr($number2, -3);

        $this->assertEquals(1, $seq1);
        $this->assertEquals(2, $seq2);
    }
}