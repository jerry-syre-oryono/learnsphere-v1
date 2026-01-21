<?php

namespace Tests\Unit\Services\Grading;

use Tests\TestCase;
use App\Models\User;
use App\Services\Grading\AcademicStandingResolver;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcademicStandingResolverTest extends TestCase
{
    use RefreshDatabase;

    private AcademicStandingResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new AcademicStandingResolver();
    }

    #[Test]
    public function it_resolves_good_standing_for_cgpa_above_200()
    {
        $student = User::factory()->create();
        $result = $this->resolver->resolve($student, 2.5);

        $this->assertEquals('normal', $result['standing']);
        $this->assertEquals('Good Standing', $result['status']);
        $this->assertFalse($result['on_probation']);
    }

    #[Test]
    public function it_resolves_probation_for_cgpa_below_200()
    {
        $student = User::factory()->create();
        $result = $this->resolver->resolve($student, 1.9);

        $this->assertEquals('probation', $result['standing']);
        $this->assertEquals('Academic Probation', $result['status']);
        $this->assertTrue($result['on_probation']);
    }

    #[Test]
    public function it_resolves_discontinued_for_discontinued_students()
    {
        $student = User::factory()->create(['is_discontinued' => true]);
        $result = $this->resolver->resolve($student, 3.0);

        $this->assertEquals('discontinued', $result['standing']);
        $this->assertEquals('Discontinued', $result['status']);
        $this->assertFalse($result['on_probation']);
    }

    #[Test]
    public function it_checks_eligibility_to_continue()
    {
        $this->assertTrue($this->resolver->isEligibleToContinue(2.0));
        $this->assertTrue($this->resolver->isEligibleToContinue(3.5));
        $this->assertFalse($this->resolver->isEligibleToContinue(1.99));
    }
}
