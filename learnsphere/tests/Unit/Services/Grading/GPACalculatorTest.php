<?php

namespace Tests\Unit\Services\Grading;

use Tests\TestCase;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\User;
use App\Models\ProgramLevel;
use App\Models\StudentCourseResult;
use App\Services\Grading\GPACalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GPACalculatorTest extends TestCase
{
    use RefreshDatabase;

    private GPACalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new GPACalculator();
    }

    #[Test]
    public function it_calculates_semester_gpa_correctly()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();
        $programLevel = ProgramLevel::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'program_level_id' => $programLevel->id,
        ]);

        // Create course results
        StudentCourseResult::factory()->create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'grade_point' => 4.0,
            'grade_points_earned' => 12.0,  // 4.0 * 3.0
            'credit_units' => 3.0,
        ]);

        // Mock the courseResults relationship
        $results = StudentCourseResult::where('enrollment_id', $enrollment->id)->get();
        $gpa = $this->calculator->calculateFromResults($results);

        $this->assertEquals(4.0, $gpa);
    }

    #[Test]
    public function it_calculates_gpa_with_multiple_courses()
    {
        $results = collect([
            // Course 1: A (4.0 points) × 3 units = 12 points
            ['grade_point' => 4.0, 'credit_units' => 3.0, 'grade_points_earned' => 12.0],
            // Course 2: B (3.0 points) × 4 units = 12 points
            ['grade_point' => 3.0, 'credit_units' => 4.0, 'grade_points_earned' => 12.0],
        ]);

        $gpa = $this->calculator->calculateFromResults($results);

        // GPA = (12 + 12) / (3 + 4) = 24 / 7 = 3.43
        $this->assertEquals(3.43, $gpa);
    }

    #[Test]
    public function it_returns_zero_gpa_for_empty_results()
    {
        $gpa = $this->calculator->calculateFromResults(collect([]));

        $this->assertEquals(0.0, $gpa);
    }

    #[Test]
    public function it_validates_gpa_within_range()
    {
        $this->assertEquals(0.0, $this->calculator->validateGPA(-1));
        $this->assertEquals(5.0, $this->calculator->validateGPA(5.5));
        $this->assertEquals(3.5, $this->calculator->validateGPA(3.5));
    }
}
