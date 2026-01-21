<?php

namespace Tests\Unit\Services\Grading;

use PHPUnit\Framework\TestCase;
use App\Services\Grading\AssessmentWeightValidator;
use App\Services\Grading\ExamThresholdEnforcer;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Assessment Weight Validator Service
 *
 * Validates:
 * - Weight ranges (0-100)
 * - Weight sums (total = 100)
 * - Negative/invalid weights
 * - Missing weights
 */
class AssessmentWeightValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected AssessmentWeightValidator $validator;

    #[\PHPUnit\Framework\Attributes\Before]
    public function setUp(): void
    {
        parent::setUp();
        $this->validator = app(AssessmentWeightValidator::class);
    }

    /**
     * Test validation passes when weights sum to 100
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function validates_correct_weights(): void
    {
        $course = Course::factory()->create();

        // Add assessments with correct weights
        $course->assessments = collect([
            (object)['weight' => 30, 'title' => 'CA 1', 'id' => 1],
            (object)['weight' => 30, 'title' => 'CA 2', 'id' => 2],
            (object)['weight' => 40, 'title' => 'Exam', 'id' => 3],
        ]);

        $result = $this->validator->validate($course);

        $this->assertTrue($result['valid']);
        $this->assertEquals(100, $result['total_weight']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test validation fails when weights sum to < 100
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function fails_when_weights_sum_less_than_100(): void
    {
        $course = Course::factory()->create();

        $course->assessments = collect([
            (object)['weight' => 30, 'title' => 'CA 1', 'id' => 1],
            (object)['weight' => 40, 'title' => 'Exam', 'id' => 2],
        ]);

        $result = $this->validator->validate($course);

        $this->assertFalse($result['valid']);
        $this->assertEquals(70, $result['total_weight']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('does not equal expected total', $result['errors'][0]);
    }

    /**
     * Test validation fails when weights sum to > 100
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function fails_when_weights_sum_more_than_100(): void
    {
        $course = Course::factory()->create();

        $course->assessments = collect([
            (object)['weight' => 50, 'title' => 'CA 1', 'id' => 1],
            (object)['weight' => 60, 'title' => 'Exam', 'id' => 2],
        ]);

        $result = $this->validator->validate($course);

        $this->assertFalse($result['valid']);
        $this->assertEquals(110, $result['total_weight']);
    }

    /**
     * Test validation fails for negative weight
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function fails_for_negative_weight(): void
    {
        $course = Course::factory()->create();

        $course->assessments = collect([
            (object)['weight' => -10, 'title' => 'Invalid', 'id' => 1],
            (object)['weight' => 110, 'title' => 'Exam', 'id' => 2],
        ]);

        $result = $this->validator->validate($course);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('negative weight', $result['errors'][0]);
    }

    /**
     * Test validation fails for weight > 100
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function fails_for_weight_exceeding_100(): void
    {
        $course = Course::factory()->create();

        $course->assessments = collect([
            (object)['weight' => 150, 'title' => 'Too Heavy', 'id' => 1],
        ]);

        $result = $this->validator->validate($course);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('exceeds 100', $result['errors'][0]);
    }

    /**
     * Test warning for zero weight
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function warns_for_zero_weight(): void
    {
        $course = Course::factory()->create();

        $course->assessments = collect([
            (object)['weight' => 0, 'title' => 'Unused', 'id' => 1],
            (object)['weight' => 100, 'title' => 'Exam', 'id' => 2],
        ]);

        $result = $this->validator->validate($course);

        $this->assertTrue($result['valid']);
        $this->assertStringContainsString('zero weight', $result['warnings'][0]);
    }

    /**
     * Test assert() throws on validation failure
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function assert_throws_on_invalid_weights(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $course = Course::factory()->create();

        $course->assessments = collect([
            (object)['weight' => 50, 'title' => 'CA', 'id' => 1],
        ]);

        $this->validator->assert($course);
    }

    /**
     * Test assert() passes on valid weights
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function assert_passes_on_valid_weights(): void
    {
        $course = Course::factory()->create();

        $course->assessments = collect([
            (object)['weight' => 50, 'title' => 'CA', 'id' => 1],
            (object)['weight' => 50, 'title' => 'Exam', 'id' => 2],
        ]);

        // Should not throw
        $this->validator->assert($course);

        $this->assertTrue(true);
    }

    /**
     * Test normalization of weights from 0-100 to 0-1
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function normalizes_weights_from_100_to_1(): void
    {
        $items = collect([
            (object)['weight' => 50],
            (object)['weight' => 30],
            (object)['weight' => 20],
        ]);

        $normalized = $this->validator->normalize($items);

        $this->assertEquals(0.5, $normalized[0]->weight);
        $this->assertEquals(0.3, $normalized[1]->weight);
        $this->assertEquals(0.2, $normalized[2]->weight);
    }

    /**
     * Test detection of normalized weights (0-1 range)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function detects_normalized_weights(): void
    {
        $items = collect([
            (object)['weight' => 0.5],
            (object)['weight' => 0.3],
            (object)['weight' => 0.2],
        ]);

        $this->assertTrue($this->validator->areNormalized($items));

        $items2 = collect([
            (object)['weight' => 50],
            (object)['weight' => 30],
            (object)['weight' => 20],
        ]);

        $this->assertFalse($this->validator->areNormalized($items2));
    }
}
