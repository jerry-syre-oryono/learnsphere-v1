<?php

namespace Tests\Feature\Services\Grading;

use Tests\TestCase;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\ProgramLevel;
use App\Models\StudentCourseResult;
use App\Services\Grading\GradeCalculator;
use App\Services\Grading\GPACalculator;
use App\Services\Grading\CGPACalculator;
use App\Services\Grading\ClassificationResolver;
use App\Services\Grading\GradeBoundaryResolver;
use App\Services\Grading\RetakeCapEnforcer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GradingEngineFeatureTest extends TestCase
{
    use RefreshDatabase;

    private GradeCalculator $gradeCalculator;
    private GPACalculator $gpaCalculator;
    private CGPACalculator $cgpaCalculator;
    private ClassificationResolver $classificationResolver;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeder to populate default grading rules
        $this->seed(\Database\Seeders\GradingSeeder::class);

        $this->gradeCalculator = new GradeCalculator(
            new GradeBoundaryResolver(),
            new RetakeCapEnforcer()
        );
        $this->gpaCalculator = new GPACalculator();
        $this->cgpaCalculator = new CGPACalculator();
        $this->classificationResolver = new ClassificationResolver();
    }

    #[Test]
    public function it_calculates_semester_gpa_correctly()
    {
        $student = User::factory()->create();
        $programLevel = ProgramLevel::where('code', 'DEG')->first();
        $enrollment = Enrollment::factory()->create([
            'user_id' => $student->id,
            'program_level_id' => $programLevel->id,
        ]);

        // Create multiple course results
        StudentCourseResult::factory()->create([
            'enrollment_id' => $enrollment->id,
            'grade_point' => 4.0,
            'grade_points_earned' => 12.0,
            'credit_units' => 3.0,
            'semester' => '2024-2025-1',
        ]);

        StudentCourseResult::factory()->create([
            'enrollment_id' => $enrollment->id,
            'grade_point' => 3.5,
            'grade_points_earned' => 14.0,
            'credit_units' => 4.0,
            'semester' => '2024-2025-1',
        ]);

        $results = StudentCourseResult::where('enrollment_id', $enrollment->id)
            ->where('semester', '2024-2025-1')
            ->get();

        $gpa = $this->gpaCalculator->calculateFromResults($results);

        // GPA = (12 + 14) / (3 + 4) = 26 / 7 = 3.71
        $this->assertGreaterThan(3.70, $gpa);
        $this->assertLessThan(3.72, $gpa);
    }

    #[Test]
    public function it_classifies_student_correctly_for_degree_program()
    {
        $classification = $this->classificationResolver->resolve(4.5, 'degree');

        $this->assertEquals('First Class', $classification['class']);
    }

    #[Test]
    public function it_classifies_student_correctly_for_diploma_program()
    {
        $classification = $this->classificationResolver->resolve(3.8, 'diploma');

        $this->assertEquals('Credit', $classification['classification']);
    }

    #[Test]
    public function it_handles_complete_grading_workflow()
    {
        // Student scenario:
        // - Taking 3 courses
        // - Course 1: 85% → A (5.0)
        // - Course 2: 72% → B (4.0)
        // - Course 3: 45% → F (0.0)

        $programLevel = ProgramLevel::where('code', 'DEG')->first();

        // Course 1: A grade
        $result1 = $this->gradeCalculator->calculate(85, $programLevel, 3.0);
        $this->assertEquals('A', $result1['letter_grade']);
        $this->assertEquals(5.0, $result1['grade_point']);

        // Course 2: B grade
        $result2 = $this->gradeCalculator->calculate(72, $programLevel, 3.0);
        $this->assertEquals('B', $result2['letter_grade']);
        $this->assertEquals(4.0, $result2['grade_point']);

        // Course 3: F grade
        $result3 = $this->gradeCalculator->calculate(45, $programLevel, 3.0);
        $this->assertEquals('F', $result3['letter_grade']);
        $this->assertEquals(0.0, $result3['grade_point']);

        // Calculate GPA
        $results = collect([
            ['grade_point' => $result1['grade_point'], 'grade_points_earned' => $result1['grade_points_earned'], 'credit_units' => 3.0],
            ['grade_point' => $result2['grade_point'], 'grade_points_earned' => $result2['grade_points_earned'], 'credit_units' => 3.0],
            ['grade_point' => $result3['grade_point'], 'grade_points_earned' => $result3['grade_points_earned'], 'credit_units' => 3.0],
        ]);

        $gpa = $this->gpaCalculator->calculateFromResults($results);

        // GPA = (15 + 12 + 0) / 9 = 27 / 9 = 3.0
        $this->assertEquals(3.0, $gpa);

        // Classify
        $classification = $this->classificationResolver->resolve($gpa, 'degree');
        $this->assertEquals('Second Class Lower', $classification['class']);
    }

    #[Test]
    public function it_applies_retake_cap_in_complete_workflow()
    {
        $programLevel = ProgramLevel::where('code', 'DEG')->first();

        // First attempt: A (5.0)
        $firstAttempt = $this->gradeCalculator->calculate(95, $programLevel, 3.0, false);
        $this->assertEquals('A', $firstAttempt['letter_grade']);

        // Retake: Should be capped at C
        $retake = $this->gradeCalculator->calculate(90, $programLevel, 3.0, true);
        $this->assertEquals('C', $retake['letter_grade']);
        $this->assertEquals(3.0, $retake['grade_point']);
        $this->assertTrue($retake['was_capped']);
    }
}
