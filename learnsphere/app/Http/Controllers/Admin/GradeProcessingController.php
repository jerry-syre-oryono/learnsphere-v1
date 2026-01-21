<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\StudentCourseResult;
use App\Services\Grading\GradingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Grade Processing Controller
 *
 * Handles grade entry and processing (admin/instructor only).
 * All actual grade calculations delegated to GradingService.
 */
class GradeProcessingController extends Controller
{
    public function __construct(private GradingService $gradingService) {}

    /**
     * Process and store student grade for a course.
     *
     * POST /api/admin/grades/process
     */
    public function processGrade(Request $request)
    {
        $this->authorize('processGrades', Enrollment::class);

        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'percentage_mark' => 'required|numeric|min:0|max:100',
            'credit_units' => 'nullable|numeric|min:0.5|max:10',
            'is_retake' => 'boolean',
            'semester' => 'nullable|string',
        ]);

        try {
            $enrollment = Enrollment::with('course', 'programLevel')->find($validated['enrollment_id']);

            $result = $this->gradingService->processStudentGrade(
                $enrollment,
                $validated['percentage_mark'],
                $validated['credit_units'] ?? 3.0,
                $validated['is_retake'] ?? false,
                $validated['semester']
            );

            return response()->json([
                'success' => true,
                'message' => 'Grade processed successfully',
                'data' => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process grade: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk process grades for multiple students in a course.
     *
     * POST /api/admin/grades/bulk-process
     */
    public function bulkProcessGrades(Request $request)
    {
        $this->authorize('processGrades', Enrollment::class);

        $validated = $request->validate([
            'grades' => 'required|array|min:1',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.percentage_mark' => 'required|numeric|min:0|max:100',
            'grades.*.credit_units' => 'nullable|numeric|min:0.5|max:10',
            'grades.*.is_retake' => 'boolean',
            'semester' => 'nullable|string',
        ]);

        $results = [];
        $failed = [];

        foreach ($validated['grades'] as $gradeData) {
            try {
                $enrollment = Enrollment::with('course', 'programLevel')
                    ->find($gradeData['enrollment_id']);

                $result = $this->gradingService->processStudentGrade(
                    $enrollment,
                    $gradeData['percentage_mark'],
                    $gradeData['credit_units'] ?? 3.0,
                    $gradeData['is_retake'] ?? false,
                    $validated['semester']
                );

                $results[] = $result;
            } catch (\Exception $e) {
                $failed[] = [
                    'enrollment_id' => $gradeData['enrollment_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($results) . ' grades processed successfully',
            'processed_count' => count($results),
            'failed_count' => count($failed),
            'results' => $results,
            'failed' => $failed,
        ], 201);
    }

    /**
     * Get course results for a specific course.
     *
     * GET /api/admin/courses/{course}/results
     */
    public function getCourseResults(Request $request)
    {
        $this->authorize('viewGrades', \App\Models\Course::class);

        $courseId = $request->route('course');
        $semester = $request->query('semester');

        $results = StudentCourseResult::where('course_id', $courseId)
            ->with(['enrollment.user', 'course'])
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Update a student's grade (allows corrections).
     *
     * PUT /api/admin/results/{result}
     */
    public function updateGrade(StudentCourseResult $result, Request $request)
    {
        $this->authorize('updateGrades', $result);

        $validated = $request->validate([
            'percentage_mark' => 'required|numeric|min:0|max:100',
            'is_retake' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $enrollment = $result->enrollment;

            $gradeResult = $this->gradingService->processStudentGrade(
                $enrollment,
                $validated['percentage_mark'],
                $result->credit_units,
                $validated['is_retake'],
                $result->semester
            );

            return response()->json([
                'success' => true,
                'message' => 'Grade updated successfully',
                'data' => $gradeResult,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update grade: ' . $e->getMessage(),
            ], 422);
        }
    }
}
