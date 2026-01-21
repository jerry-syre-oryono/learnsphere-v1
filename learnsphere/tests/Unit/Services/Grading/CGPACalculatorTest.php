<?php

namespace Tests\Unit\Services\Grading;

use Tests\TestCase;
use App\Services\Grading\CGPACalculator;
use PHPUnit\Framework\Attributes\Test;

class CGPACalculatorTest extends TestCase
{
    private CGPACalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CGPACalculator();
    }

    #[Test]
    public function it_calculates_cgpa_from_course_results()
    {
        $results = collect([
            // Semester 1
            ['grade_points_earned' => 12.0, 'credit_units' => 3.0],
            ['grade_points_earned' => 12.0, 'credit_units' => 4.0],
            // Semester 2
            ['grade_points_earned' => 9.0, 'credit_units' => 3.0],
        ]);

        $cgpa = $this->calculator->calculateFromResults($results);

        // CGPA = (12 + 12 + 9) / (3 + 4 + 3) = 33 / 10 = 3.3
        $this->assertEquals(3.3, $cgpa);
    }

    #[Test]
    public function it_returns_zero_cgpa_for_empty_results()
    {
        $cgpa = $this->calculator->calculateFromResults(collect([]));

        $this->assertEquals(0.0, $cgpa);
    }

    #[Test]
    public function it_checks_graduation_eligibility_correctly()
    {
        $this->assertTrue($this->calculator->isEligibleForGraduation(2.0));
        $this->assertTrue($this->calculator->isEligibleForGraduation(3.5));
        $this->assertFalse($this->calculator->isEligibleForGraduation(1.99));
        $this->assertFalse($this->calculator->isEligibleForGraduation(0.0));
    }

    #[Test]
    public function it_includes_failed_courses_in_cgpa_calculation()
    {
        $results = collect([
            // Passing course
            ['grade_points_earned' => 12.0, 'credit_units' => 3.0],
            // Failed course (contributes 0)
            ['grade_points_earned' => 0.0, 'credit_units' => 3.0],
        ]);

        $cgpa = $this->calculator->calculateFromResults($results);

        // CGPA = (12 + 0) / (3 + 3) = 12 / 6 = 2.0
        $this->assertEquals(2.0, $cgpa);
    }
}
