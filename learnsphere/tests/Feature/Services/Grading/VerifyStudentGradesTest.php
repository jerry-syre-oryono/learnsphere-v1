<?php

namespace Tests\Feature\Services\Grading;

use Tests\TestCase;
use App\Models\User;
use App\Models\StudentCourseResult;
use App\Services\Grading\GPACalculator;
use App\Services\Grading\CGPACalculator;
use App\Services\Grading\ClassificationResolver;
use App\Services\Grading\AcademicStandingResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyStudentGradesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that verifies all seeded student grades are calculated correctly.
     */
    public function test_seeded_student_grades_calculate_correctly()
    {
        $gpaCalculator = new GPACalculator();
        $cgpaCalculator = new CGPACalculator();
        $classificationResolver = new ClassificationResolver();
        $standingResolver = new AcademicStandingResolver();

        // Get all students with grades
        $students = User::query()
            ->where('is_approved', true)
            ->whereHas('roles', function($q) {
                $q->where('name', 'student');
            })
            ->get();

        $this->assertTrue($students->count() > 0, 'No approved students found');

        echo "\n\n=== STUDENT GRADES VERIFICATION ===\n";

        foreach ($students as $student) {
            // Get all course results for this student
            $courseResults = StudentCourseResult::query()
                ->whereHas('enrollment', function($q) use ($student) {
                    $q->where('user_id', $student->id);
                })
                ->get();

            if ($courseResults->isEmpty()) {
                echo "\n❌ {$student->name}: No grades found\n";
                continue;
            }

            echo "\n📊 {$student->name}:\n";
            echo "   Courses taken: {$courseResults->count()}\n";

            // Calculate GPA
            $gpa = $gpaCalculator->calculateFromResults($courseResults);
            $cgpa = $cgpaCalculator->calculateFromResults($courseResults);

            echo "   GPA: " . number_format($gpa, 2) . "\n";
            echo "   CGPA: " . number_format($cgpa, 2) . "\n";

            // Get classification
            $classification = $classificationResolver->resolve($cgpa, 'degree');
            echo "   Classification: {$classification['class']}\n";

            // Get academic standing
            $standing = $standingResolver->resolve($student, $cgpa);
            echo "   Standing: {$standing['status']}\n";

            // Show individual course results
            echo "   Courses:\n";
            foreach ($courseResults as $result) {
                echo "     • {$result->course->name}: {$result->final_mark}% → {$result->letter_grade} ({$result->grade_point} pts, {$result->grade_points_earned} earned)\n";

                if ($result->was_capped) {
                    echo "       ⚠️  Capped from {$result->original_grade} to {$result->capped_grade}\n";
                }
            }

            // Verify calculations
            $expectedGPA = $courseResults->sum('grade_points_earned') / $courseResults->sum('credit_units');
            $this->assertEquals(
                round($expectedGPA, 2),
                $gpa,
                "GPA mismatch for {$student->name}: expected " . round($expectedGPA, 2) . " but got {$gpa}"
            );
        }

        echo "\n✅ All student grades verified successfully!\n\n";
        $this->assertTrue(true);
    }
}
