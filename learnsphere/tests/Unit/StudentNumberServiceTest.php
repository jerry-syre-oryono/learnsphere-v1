<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\StudentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    private StudentNumberService $studentNumberService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->studentNumberService = app(StudentNumberService::class);
    }

    public function test_course_code_generation()
    {
        $course = Course::factory()->create(['title' => 'Diploma in VFX']);
        $this->assertEquals('DVFX', $course->getCourseCode());

        $course2 = Course::factory()->create(['title' => 'Certificate in Web Development']);
        $this->assertEquals('CWD', $course2->getCourseCode());

        $course3 = Course::factory()->create(['title' => 'Bachelor of Computer Science']);
        $this->assertEquals('BCS', $course3->getCourseCode());
    }

    public function test_student_number_format()
    {
        $course = Course::factory()->create(['title' => 'Diploma in VFX']);
        $user = User::factory()->create();

        // Create enrollment manually first
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $studentNumber = $this->studentNumberService->generateStudentNumber($user, $course);

        // Should be in format: DVFX-S-2026-001
        $this->assertMatchesRegularExpression('/^DVFX-S-\d{4}-\d{3}$/', $studentNumber);

        // Verify the enrollment was updated
        $enrollment->refresh();
        $this->assertEquals($studentNumber, $enrollment->student_number);
        $this->assertEquals(now()->year, $enrollment->enrollment_year);
    }

    public function test_sequential_numbering()
    {
        $course = Course::factory()->create(['title' => 'Diploma in VFX']);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create enrollments
        Enrollment::create(['user_id' => $user1->id, 'course_id' => $course->id]);
        Enrollment::create(['user_id' => $user2->id, 'course_id' => $course->id]);

        $number1 = $this->studentNumberService->generateStudentNumber($user1, $course);
        $number2 = $this->studentNumberService->generateStudentNumber($user2, $course);

        // Extract sequence numbers
        $seq1 = (int) substr($number1, -3);
        $seq2 = (int) substr($number2, -3);

        $this->assertEquals(1, $seq1);
        $this->assertEquals(2, $seq2);
    }

    public function test_reuse_existing_student_number()
    {
        $course = Course::factory()->create(['title' => 'Diploma in VFX']);
        $user = User::factory()->create();

        // Create enrollment with existing student number
        $existingNumber = 'DVFX-S-' . now()->year . '-005';
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_number' => $existingNumber,
            'enrollment_year' => now()->year,
        ]);

        // Try to generate again - should return existing number
        $generatedNumber = $this->studentNumberService->generateStudentNumber($user, $course);

        $this->assertEquals($existingNumber, $generatedNumber);
    }

    public function test_yearly_reset()
    {
        $course = Course::factory()->create(['title' => 'Diploma in VFX']);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate previous year enrollment
        $lastYear = now()->year - 1;
        Enrollment::create([
            'user_id' => $user1->id,
            'course_id' => $course->id,
            'student_number' => 'DVFX-S-' . $lastYear . '-001',
            'enrollment_year' => $lastYear,
        ]);

        // Create new enrollment for current year
        Enrollment::create(['user_id' => $user2->id, 'course_id' => $course->id]);

        $number = $this->studentNumberService->generateStudentNumber($user2, $course);

        // Should start from 001 for the new year
        $this->assertStringEndsWith('001', $number);
        $this->assertStringContainsString((string)($lastYear + 1), $number);
    }
}