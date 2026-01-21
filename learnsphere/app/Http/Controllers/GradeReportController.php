<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use App\Services\Grading\GradingService;
use Illuminate\Http\Request;

/**
 * Grade Report Controller
 *
 * Handles read-only grade report endpoints.
 * No modification of grades through this controller.
 */
class GradeReportController extends Controller
{
    public function __construct(private GradingService $gradingService) {}

    /**
     * Get complete grade report for authenticated student.
     *
     * GET /api/student/grade-report
     */
    public function getStudentGradeReport(Request $request)
    {
        $student = $request->user();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $report = $this->gradingService->getCompleteGradeReport($student);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get grade report for a specific enrollment.
     *
     * GET /api/enrollments/{enrollment}/grades
     */
    public function getEnrollmentGrades(Enrollment $enrollment, Request $request)
    {
        // Check authorization
        if ($request->user()->id !== $enrollment->user_id && !$request->user()->hasRole('instructor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $semester = $request->query('semester');
        $report = $this->gradingService->getEnrollmentGradeReport($enrollment, $semester);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get CGPA for a student.
     *
     * GET /api/students/{student}/cgpa
     */
    public function getStudentCGPA(User $student, Request $request)
    {
        if ($request->user()->id !== $student->id && !$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cgpa = $this->gradingService->calculateCGPA($student);
        $classification = $this->gradingService->getAcademicClassification($student);
        $standing = $this->gradingService->getAcademicStanding($student);

        return response()->json([
            'success' => true,
            'data' => [
                'cgpa' => $cgpa,
                'classification' => $classification,
                'standing' => $standing,
            ],
        ]);
    }

    /**
     * Get academic policies (read-only).
     *
     * GET /api/academic-policies
     */
    public function getAcademicPolicies(Request $request)
    {
        $policies = \App\Models\AcademicPolicy::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $policies,
        ]);
    }
}
