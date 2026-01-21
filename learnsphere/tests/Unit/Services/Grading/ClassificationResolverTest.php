<?php

namespace Tests\Unit\Services\Grading;

use Tests\TestCase;
use App\Models\ProgramLevel;
use App\Models\AcademicClassification;
use App\Services\Grading\ClassificationResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ClassificationResolverTest extends TestCase
{
    use RefreshDatabase;

    private ClassificationResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ClassificationResolver();
    }

    #[Test]
    public function it_resolves_distinction_for_diploma_above_400_cgpa()
    {
        $result = $this->resolver->resolve(4.5, 'diploma');

        $this->assertEquals('Distinction', $result['classification']);
    }

    #[Test]
    public function it_resolves_credit_for_diploma_300_to_399_cgpa()
    {
        $result = $this->resolver->resolve(3.5, 'diploma');

        $this->assertEquals('Credit', $result['classification']);
    }

    #[Test]
    public function it_resolves_pass_for_diploma_200_to_299_cgpa()
    {
        $result = $this->resolver->resolve(2.5, 'diploma');

        $this->assertEquals('Pass', $result['classification']);
    }

    #[Test]
    public function it_resolves_fail_for_diploma_below_200_cgpa()
    {
        $result = $this->resolver->resolve(1.5, 'diploma');

        $this->assertEquals('Fail', $result['classification']);
    }

    #[Test]
    public function it_resolves_first_class_for_degree_above_440_cgpa()
    {
        $result = $this->resolver->resolve(4.6, 'degree');

        $this->assertEquals('First Class', $result['class']);
    }

    #[Test]
    public function it_resolves_second_class_upper_for_degree_360_to_439_cgpa()
    {
        $result = $this->resolver->resolve(4.0, 'degree');

        $this->assertEquals('Second Class Upper', $result['class']);
    }

    #[Test]
    public function it_resolves_second_class_lower_for_degree_280_to_359_cgpa()
    {
        $result = $this->resolver->resolve(3.2, 'degree');

        $this->assertEquals('Second Class Lower', $result['class']);
    }

    #[Test]
    public function it_resolves_pass_for_degree_200_to_279_cgpa()
    {
        $result = $this->resolver->resolve(2.4, 'degree');

        $this->assertEquals('Pass', $result['class']);
    }

    #[Test]
    public function it_resolves_fail_for_degree_below_200_cgpa()
    {
        $result = $this->resolver->resolve(1.5, 'degree');

        $this->assertEquals('Fail', $result['class']);
    }

    #[Test]
    public function it_handles_certificate_programs_without_classification()
    {
        $result = $this->resolver->resolve(3.5, 'certificate');

        $this->assertNull($result['classification']);
        $this->assertNull($result['class']);
    }
}
