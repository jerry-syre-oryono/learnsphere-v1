<?php

namespace Tests\Unit\Services\Grading;

use Tests\TestCase;
use App\Models\ProgramLevel;
use App\Models\GradingRule;
use App\Services\Grading\GradeBoundaryResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GradeBoundaryResolverTest extends TestCase
{
    use RefreshDatabase;

    private GradeBoundaryResolver $resolver;
    private ProgramLevel $programLevel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new GradeBoundaryResolver();
        $this->programLevel = ProgramLevel::factory()->create(['code' => 'DEG']);
    }

    #[Test]
    public function it_resolves_grade_a_for_mark_80_or_above()
    {
        $result = $this->resolver->resolve(80, $this->programLevel);
        $this->assertEquals('A', $result['grade']);
        $this->assertEquals(5.0, $result['points']);

        $result = $this->resolver->resolve(100, $this->programLevel);
        $this->assertEquals('A', $result['grade']);
        $this->assertEquals(5.0, $result['points']);
    }

    #[Test]
    public function it_resolves_grade_b_plus_for_mark_75_to_79()
    {
        $result = $this->resolver->resolve(75, $this->programLevel);
        $this->assertEquals('B+', $result['grade']);
        $this->assertEquals(4.5, $result['points']);

        $result = $this->resolver->resolve(79, $this->programLevel);
        $this->assertEquals('B+', $result['grade']);
        $this->assertEquals(4.5, $result['points']);
    }

    #[Test]
    public function it_resolves_grade_c_for_mark_60_to_64()
    {
        $result = $this->resolver->resolve(60, $this->programLevel);
        $this->assertEquals('C', $result['grade']);
        $this->assertEquals(3.0, $result['points']);

        $result = $this->resolver->resolve(64, $this->programLevel);
        $this->assertEquals('C', $result['grade']);
        $this->assertEquals(3.0, $result['points']);
    }

    #[Test]
    public function it_resolves_grade_f_for_mark_below_50()
    {
        $result = $this->resolver->resolve(49, $this->programLevel);
        $this->assertEquals('F', $result['grade']);
        $this->assertEquals(0.0, $result['points']);

        $result = $this->resolver->resolve(0, $this->programLevel);
        $this->assertEquals('F', $result['grade']);
        $this->assertEquals(0.0, $result['points']);
    }

    #[Test]
    public function it_handles_edge_cases_correctly()
    {
        // Edge case: 49.9 should be F
        $result = $this->resolver->resolve(49.9, $this->programLevel);
        $this->assertEquals('F', $result['grade']);

        // Edge case: 50 should be D
        $result = $this->resolver->resolve(50, $this->programLevel);
        $this->assertEquals('D', $result['grade']);
        $this->assertEquals(2.0, $result['points']);
    }
}
