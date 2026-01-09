<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentStudentNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_creates_student_number()
    {
        // Create a course and user
        $course = Course::factory()->create(['title' => 'Test Course']);
        $user = User::factory()->create();

        // Create enrollment directly (bypassing the UI role check)
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        // Manually trigger student number generation (simulating what CourseDisplay does)
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

    public function test_multiple_enrollments_get_sequential_numbers()
    {
        $course = Course::factory()->create(['title' => 'Test Course']);
        $studentNumberService = app(\App\Services\StudentNumberService::class);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create enrollments
        Enrollment::create(['user_id' => $user1->id, 'course_id' => $course->id]);
        Enrollment::create(['user_id' => $user2->id, 'course_id' => $course->id]);

        // Generate student numbers
        $number1 = $studentNumberService->generateStudentNumber($user1, $course);
        $number2 = $studentNumberService->generateStudentNumber($user2, $course);

        // Extract sequence numbers
        $seq1 = (int) substr($number1, -3);
        $seq2 = (int) substr($number2, -3);

        $this->assertEquals(1, $seq1);
        $this->assertEquals(2, $seq2);
    }
}