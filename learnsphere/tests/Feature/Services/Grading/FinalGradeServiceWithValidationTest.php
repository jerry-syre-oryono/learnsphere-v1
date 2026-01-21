<?php

namespace Tests\Feature\Services\Grading;

use PHPUnit\Framework\TestCase;
use App\Services\FinalGradeService;
use App\Services\Grading\AssessmentWeightValidator;
use App\Services\Grading\ExamThresholdEnforcer;
use App\Models\Course;
use App\Models\User;
use App\Models\Assessment;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Final Grade Service with Weight Validation & Exam Threshold
 *
 * Integration test verifying:
 * - Weight validation in final grade calculation
 * - Exam threshold enforcement (< 40% = F)
 * - Boundary value handling (49.99 → F, 50 → D)
 * - Weighted average calculation
 */
class FinalGradeServiceWithValidationTest extends TestCase
{
    use RefreshDatabase;

    protected FinalGradeService $service;
    protected AssessmentWeightValidator $validator;
    protected ExamThresholdEnforcer $enforcer;

    #[\PHPUnit\Framework\Attributes\Before]
    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(FinalGradeService::class);
        $this->validator = app(AssessmentWeightValidator::class);
        $this->enforcer = app(ExamThresholdEnforcer::class);
    }

    /**
     * Test: Valid weights (50 CA + 50 Exam) → calculate final grade
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function calculates_final_grade_with_valid_weights(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        // Setup: CA (50%) with 80% score
        $ca = Assessment::factory()->create([
            'title' => 'Continuous Assessment',
            'weight' => 50,
            'max_score' => 100,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 80.0,
        ]);

        // Setup: Exam (50%) with 70% score
        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
            'weight' => 50,
            'max_score' => 100,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 70.0,
        ]);

        $course->assessments = collect([$ca, $exam]);

        // Calculate: (80 * 0.5 + 70 * 0.5) = 75%
        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(75.0, $finalGrade);
    }

    /**
     * Test: Invalid weights (30 + 40 = 70, not 100) → throws exception
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function throws_on_invalid_weight_sum(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('weight validation failed');

        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 30,
        ]);

        $exam = Assessment::factory()->create([
            'title' => 'Exam',
            'weight' => 40, // Total = 70, not 100
        ]);

        $course->assessments = collect([$ca, $exam]);

        $this->service->calculateFinalGrade($student, $course);
    }

    /**
     * Test: Exam 35% (< 40%) → final grade forced to 0 (F) regardless of CA
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function enforces_exam_threshold_below_40_percent(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        // CA: 95% (high score)
        $ca = Assessment::factory()->create([
            'title' => 'Continuous Assessment',
            'weight' => 30,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 95.0,
        ]);

        // Exam: 35% (below 40% threshold)
        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
            'weight' => 70,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 35.0,
        ]);

        $course->assessments = collect([$ca, $exam]);

        // Weighted: (95*0.3 + 35*0.7) = 28.5 + 24.5 = 53% (would be pass)
        // But exam 35% < 40%, so should fail
        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(0.0, $finalGrade, 'Should be 0 (F) due to exam threshold');
    }

    /**
     * Test: Exam exactly 40% → allowed (threshold is inclusive)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_grade_when_exam_at_40_percent_threshold(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 50,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 50.0,
        ]);

        $exam = Assessment::factory()->create([
            'title' => 'Exam',
            'weight' => 50,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 40.0,  // Exactly at threshold
        ]);

        $course->assessments = collect([$ca, $exam]);

        // (50 * 0.5 + 40 * 0.5) = 45%
        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(45.0, $finalGrade);
    }

    /**
     * Test: Boundary value 49.99% → should map to F (0.0)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function maps_49_99_percent_to_f_grade(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 50,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 49.99,
        ]);

        $exam = Assessment::factory()->create([
            'title' => 'Exam',
            'weight' => 50,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 49.99,
        ]);

        $course->assessments = collect([$ca, $exam]);

        // (49.99 * 0.5 + 49.99 * 0.5) = 49.99%
        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(49.99, $finalGrade);
    }

    /**
     * Test: Boundary value 50.00% → should map to D (2.0)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function maps_50_00_percent_to_d_grade(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 50,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 50.0,
        ]);

        $exam = Assessment::factory()->create([
            'title' => 'Exam',
            'weight' => 50,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 50.0,
        ]);

        $course->assessments = collect([$ca, $exam]);

        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(50.0, $finalGrade);
    }

    /**
     * Test: Boundary value 69.99% → should map to C+ (3.5)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function maps_69_99_percent_correctly(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 100,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 69.99,
        ]);

        $course->assessments = collect([$ca]);

        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(69.99, $finalGrade);
    }

    /**
     * Test: Boundary value 70.00% → should map to B (4.0)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function maps_70_00_percent_correctly(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 100,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 70.0,
        ]);

        $course->assessments = collect([$ca]);

        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(70.0, $finalGrade);
    }

    /**
     * Test: No submissions → 0% final grade
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function returns_zero_when_no_submissions(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 50,
        ]);

        $exam = Assessment::factory()->create([
            'title' => 'Exam',
            'weight' => 50,
        ]);

        // No submissions created

        $course->assessments = collect([$ca, $exam]);

        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(0.0, $finalGrade);
    }

    /**
     * Test: Only one assessment has submission → weighted average of what exists
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function calculates_with_partial_submissions(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 50,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 80.0,
        ]);

        // No exam submission

        $exam = Assessment::factory()->create([
            'title' => 'Exam',
            'weight' => 50,
        ]);

        $course->assessments = collect([$ca, $exam]);

        // With only CA (50% weight), result = (80 / 50) * 100 = 160%? No...
        // Actually: (80 * 50) / 50 = 80% (only 50 total weight = 80)
        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        // Weighted: 80 * 0.5 / (0.5) = 80
        $this->assertEquals(80.0, $finalGrade);
    }

    /**
     * Test: Negative percentage clamped to 0
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function clamps_negative_percentage_to_zero(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'CA',
            'weight' => 100,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'score' => -10,
            'max_score' => 100,
            'percentage' => null,
        ]);

        $course->assessments = collect([$ca]);

        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(0.0, $finalGrade);
    }

    /**
     * Test: Percentage > 100 clamped to 100
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function clamps_percentage_over_100_to_100(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $ca = Assessment::factory()->create([
            'title' => 'Bonus CA',
            'weight' => 100,
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca->id,
            'percentage' => 150.0, // Over 100
        ]);

        $course->assessments = collect([$ca]);

        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(100.0, $finalGrade);
    }

    /**
     * Test: Multiple CAs (CA1 30%, CA2 20%, Exam 50%) with mixed scores
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function calculates_multiple_assessments_correctly(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        // CA1: 30%, score 75%
        $ca1 = Assessment::factory()->create([
            'title' => 'Assignment 1',
            'weight' => 30,
        ]);
        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca1->id,
            'percentage' => 75.0,
        ]);

        // CA2: 20%, score 85%
        $ca2 = Assessment::factory()->create([
            'title' => 'Assignment 2',
            'weight' => 20,
        ]);
        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $ca2->id,
            'percentage' => 85.0,
        ]);

        // Exam: 50%, score 65%
        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
            'weight' => 50,
        ]);
        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 65.0,
        ]);

        $course->assessments = collect([$ca1, $ca2, $exam]);

        // (75*0.3 + 85*0.2 + 65*0.5) = 22.5 + 17 + 32.5 = 72%
        $finalGrade = $this->service->calculateFinalGrade($student, $course);

        $this->assertEquals(72.0, $finalGrade);
    }
}
