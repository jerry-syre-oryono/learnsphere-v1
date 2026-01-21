<?php

namespace Tests\Unit\Services\Grading;

use Tests\TestCase;
use App\Models\ProgramLevel;
use App\Services\Grading\GradeCalculator;
use App\Services\Grading\GradeBoundaryResolver;
use App\Services\Grading\RetakeCapEnforcer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GradeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private GradeCalculator $calculator;
    private ProgramLevel $programLevel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new GradeCalculator(
            new GradeBoundaryResolver(),
            new RetakeCapEnforcer()
        );
        $this->programLevel = ProgramLevel::factory()->create(['code' => 'DEG']);
    }

    #[Test]
    public function it_calculates_grade_and_grade_points_correctly()
    {
        $result = $this->calculator->calculate(85, $this->programLevel, 3.0, false);

        $this->assertEquals('A', $result['letter_grade']);
        $this->assertEquals(5.0, $result['grade_point']);
        $this->assertEquals(15.0, $result['grade_points_earned']); // 5.0 * 3.0
        $this->assertFalse($result['was_capped']);
    }

    #[Test]
    public function it_caps_grade_at_c_for_retake_courses()
    {
        $result = $this->calculator->calculate(95, $this->programLevel, 3.0, true);

        // Original grade would be A, but capped at C for retake
        $this->assertEquals('C', $result['letter_grade']);
        $this->assertEquals(3.0, $result['grade_point']);
        $this->assertEquals(9.0, $result['grade_points_earned']); // 3.0 * 3.0
        $this->assertTrue($result['was_capped']);
        $this->assertEquals('A', $result['original_grade']);
        $this->assertEquals('C', $result['capped_grade']);
    }

    #[Test]
    public function it_does_not_cap_grade_if_already_below_c()
    {
        $result = $this->calculator->calculate(62, $this->programLevel, 3.0, true);

        // C already, so no capping
        $this->assertEquals('C', $result['letter_grade']);
        $this->assertEquals(3.0, $result['grade_point']);
        $this->assertFalse($result['was_capped']);
    }

    #[Test]
    public function it_handles_failed_courses()
    {
        $result = $this->calculator->calculate(45, $this->programLevel, 3.0, false);

        $this->assertEquals('F', $result['letter_grade']);
        $this->assertEquals(0.0, $result['grade_point']);
        $this->assertEquals(0.0, $result['grade_points_earned']);
    }

    #[Test]
    public function it_respects_credit_units_in_grade_points_earned()
    {
        $result = $this->calculator->calculate(75, $this->programLevel, 4.0, false);

        $this->assertEquals('B+', $result['letter_grade']);
        $this->assertEquals(4.5, $result['grade_point']);
        $this->assertEquals(18.0, $result['grade_points_earned']); // 4.5 * 4.0
    }

    #[Test]
    public function it_clamps_marks_to_valid_range()
    {
        $result = $this->calculator->calculate(150, $this->programLevel, 3.0, false);
        $this->assertEquals('A', $result['letter_grade']);

        $result = $this->calculator->calculate(-10, $this->programLevel, 3.0, false);
        $this->assertEquals('F', $result['letter_grade']);
    }
}
