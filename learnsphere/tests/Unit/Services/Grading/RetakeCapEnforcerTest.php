<?php

namespace Tests\Unit\Services\Grading;

use Tests\TestCase;
use App\Services\Grading\RetakeCapEnforcer;
use PHPUnit\Framework\Attributes\Test;

class RetakeCapEnforcerTest extends TestCase
{
    private RetakeCapEnforcer $enforcer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enforcer = new RetakeCapEnforcer();
    }

    #[Test]
    public function it_caps_grade_above_c_to_c()
    {
        $result = $this->enforcer->enforce('A', 5.0, 3.0);

        $this->assertEquals('C', $result['letter_grade']);
        $this->assertEquals(3.0, $result['grade_point']);
        $this->assertEquals(9.0, $result['grade_points_earned']);
        $this->assertTrue($result['was_capped']);
    }

    #[Test]
    public function it_does_not_cap_grade_c_or_below()
    {
        $result = $this->enforcer->enforce('C', 3.0, 3.0);
        $this->assertEquals('C', $result['letter_grade']);
        $this->assertEquals(3.0, $result['grade_point']);
        $this->assertFalse($result['was_capped']);

        $result = $this->enforcer->enforce('D', 2.0, 3.0);
        $this->assertEquals('D', $result['letter_grade']);
        $this->assertEquals(2.0, $result['grade_point']);
        $this->assertFalse($result['was_capped']);

        $result = $this->enforcer->enforce('F', 0.0, 3.0);
        $this->assertEquals('F', $result['letter_grade']);
        $this->assertEquals(0.0, $result['grade_point']);
        $this->assertFalse($result['was_capped']);
    }

    #[Test]
    public function it_caps_grade_just_above_c()
    {
        $result = $this->enforcer->enforce('C+', 3.5, 3.0);

        $this->assertEquals('C', $result['letter_grade']);
        $this->assertEquals(3.0, $result['grade_point']);
        $this->assertTrue($result['was_capped']);
    }

    #[Test]
    public function it_correctly_calculates_grade_points_earned_with_different_credit_units()
    {
        $result = $this->enforcer->enforce('B+', 4.5, 4.0);

        $this->assertEquals('C', $result['letter_grade']);
        $this->assertEquals(3.0, $result['grade_point']);
        $this->assertEquals(12.0, $result['grade_points_earned']); // 3.0 * 4.0
    }
}
