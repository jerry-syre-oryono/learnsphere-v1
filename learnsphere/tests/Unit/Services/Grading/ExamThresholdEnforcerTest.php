<?php

namespace Tests\Unit\Services\Grading;

use PHPUnit\Framework\TestCase;
use App\Services\Grading\ExamThresholdEnforcer;
use App\Models\Course;
use App\Models\User;
use App\Models\Assessment;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Exam Threshold Enforcer Service
 *
 * NCHE Regulation: "Any student who scores < 40% on exam automatically fails
 * the course regardless of continuous assessment score."
 *
 * Validates:
 * - Exam component detection
 * - Threshold checking (< 40%)
 * - Grade override to F (0.0)
 * - Audit logging
 */
class ExamThresholdEnforcerTest extends TestCase
{
    use RefreshDatabase;

    protected ExamThresholdEnforcer $enforcer;

    #[\PHPUnit\Framework\Attributes\Before]
    public function setUp(): void
    {
        parent::setUp();
        $this->enforcer = app(ExamThresholdEnforcer::class);
    }

    /**
     * Test exam fail override: exam < 40% forces F grade
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function overrides_to_f_when_exam_below_40_percent(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        // Create exam assessment
        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
            'type' => Assessment::TYPE_EXAM,
        ]);

        // Create submission with 35% score
        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 35.0,
            'score' => 35,
            'max_score' => 100,
        ]);

        // Mock course method to return exam
        $course->assessments = collect([$exam]);

        // Calculate: CA 95% + Exam 35% = 65% weighted average
        $weightedGrade = 65.0;

        $result = $this->enforcer->enforce($weightedGrade, $student, $course);

        // Should be forced to F (0.0)
        $this->assertEquals(0.0, $result['final_grade']);
        $this->assertTrue($result['was_enforced']);
        $this->assertEquals(35.0, $result['exam_percentage']);
        $this->assertEquals(65.0, $result['original_grade']);
        $this->assertStringContainsString('below 40%', $result['audit_reason']);
    }

    /**
     * Test exam pass: exam >= 40% allows weighted average
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_weighted_grade_when_exam_at_or_above_40_percent(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
            'type' => Assessment::TYPE_EXAM,
        ]);

        // Create submission with 40% score (exactly at threshold)
        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 40.0,
        ]);

        $course->assessments = collect([$exam]);

        $weightedGrade = 65.0;

        $result = $this->enforcer->enforce($weightedGrade, $student, $course);

        // Should keep weighted grade
        $this->assertEquals(65.0, $result['final_grade']);
        $this->assertFalse($result['was_enforced']);
        $this->assertEquals(40.0, $result['exam_percentage']);
        $this->assertNull($result['original_grade']);
    }

    /**
     * Test exam 39.99% triggers fail (rounding test)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function enforces_fail_for_exam_just_below_40_percent(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 39.99,
        ]);

        $course->assessments = collect([$exam]);

        $result = $this->enforcer->enforce(75.0, $student, $course);

        $this->assertTrue($result['was_enforced']);
        $this->assertEquals(0.0, $result['final_grade']);
    }

    /**
     * Test exam 40.01% does NOT trigger fail
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function allows_pass_for_exam_just_above_40_percent(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 40.01,
        ]);

        $course->assessments = collect([$exam]);

        $result = $this->enforcer->enforce(75.0, $student, $course);

        $this->assertFalse($result['was_enforced']);
        $this->assertEquals(75.0, $result['final_grade']);
    }

    /**
     * Test high CA, low exam: exam threshold overrides CA score
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function overrides_high_ca_with_low_exam(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        // Scenario: CA 95%, Exam 25%
        // Weighted (CA 50%, Exam 50%): (95*0.5 + 25*0.5) = 60%
        // But exam < 40%, so should fail

        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 25.0,
        ]);

        $course->assessments = collect([$exam]);

        // 60% is pass normally, but exam < 40% should override
        $result = $this->enforcer->enforce(60.0, $student, $course);

        $this->assertTrue($result['was_enforced']);
        $this->assertEquals(0.0, $result['final_grade']);
        $this->assertEquals(60.0, $result['original_grade']);
    }

    /**
     * Test no exam component: enforcement skipped gracefully
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_missing_exam_gracefully(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        // No exam in course
        $course->assessments = collect([
            (object)['title' => 'CA Assessment'],
        ]);

        $result = $this->enforcer->enforce(65.0, $student, $course);

        // Should not enforce, no exam found
        $this->assertFalse($result['was_enforced']);
        $this->assertEquals(65.0, $result['final_grade']);
        $this->assertStringContainsString('No exam component', $result['reason']);
    }

    /**
     * Test no submission for exam: enforcement skipped
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function handles_missing_exam_submission(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
        ]);

        // No submission created

        $course->assessments = collect([$exam]);

        $result = $this->enforcer->enforce(75.0, $student, $course);

        $this->assertFalse($result['was_enforced']);
        $this->assertStringContainsString('No exam submission', $result['reason']);
    }

    /**
     * Test assertHasExam throws when exam missing
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function assert_has_exam_throws_when_missing(): void
    {
        $this->expectException(\RuntimeException::class);

        $course = Course::factory()->create();
        $course->assessments = collect([]);

        $this->enforcer->assertHasExam($course);
    }

    /**
     * Test assertHasExam passes when exam exists
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function assert_has_exam_passes_when_found(): void
    {
        $course = Course::factory()->create();

        $exam = Assessment::factory()->create([
            'title' => 'Final Exam',
            'type' => Assessment::TYPE_EXAM,
        ]);

        $course->assessments = collect([$exam]);

        $result = $this->enforcer->assertHasExam($course);

        $this->assertInstanceOf(Assessment::class, $result);
        $this->assertEquals($exam->id, $result->id);
    }

    /**
     * Test checkExamThreshold returns correct info
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function checkExamThreshold_returns_correct_data(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $exam = Assessment::factory()->create([
            'title' => 'Midterm Exam',
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 35.5,
        ]);

        $course->assessments = collect([$exam]);

        $result = $this->enforcer->checkExamThreshold($student, $course);

        $this->assertTrue($result['should_enforce']);
        $this->assertEquals(35.5, $result['exam_percentage']);
        $this->assertStringContainsString('Midterm Exam', $result['exam_name']);
    }

    /**
     * Test keyword detection: "exam", "test", "midterm" recognized
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function recognizes_exam_by_keyword(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        // Try with "Midterm" keyword
        $exam = Assessment::factory()->create([
            'title' => 'Midterm Assessment',
        ]);

        Submission::factory()->create([
            'user_id' => $student->id,
            'submittable_type' => Assessment::class,
            'submittable_id' => $exam->id,
            'percentage' => 35.0,
        ]);

        $course->assessments = collect([$exam]);

        $result = $this->enforcer->checkExamThreshold($student, $course);

        $this->assertTrue($result['should_enforce']);
    }
}
