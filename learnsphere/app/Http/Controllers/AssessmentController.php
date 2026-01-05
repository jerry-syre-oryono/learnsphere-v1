<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Quiz;
use App\Models\Submission;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    use AuthorizesRequests;

    protected AssessmentService $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * Store a newly created quiz/exam.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:quiz,exam',
            'time_limit' => 'nullable|integer|min:0',
            'max_attempts' => 'nullable|integer|min:1',
            'randomize' => 'nullable|boolean',
            'questions_per_attempt' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|numeric|min:0|max:100',
            'weight' => 'nullable|numeric|min:0',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
            'is_published' => 'nullable|boolean',
            'show_answers_after_submit' => 'nullable|boolean',
        ]);

        $validated['lesson_id'] = $lesson->id;
        $quiz = $this->assessmentService->createQuiz($validated);

        return response()->json([
            'success' => true,
            'message' => 'Assessment created successfully',
            'quiz' => $quiz,
        ]);
    }

    /**
     * Add a question to a quiz.
     */
    public function addQuestion(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz->lesson->course);

        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string|in:mcq,multiple,short_answer,essay',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'correct_answer' => 'nullable',
            'points' => 'nullable|numeric|min:1',
            'order' => 'nullable|integer',
            'explanation' => 'nullable|string',
        ]);

        $question = $this->assessmentService->addQuestion($quiz, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Question added successfully',
            'question' => $question,
        ]);
    }

    /**
     * Update a question.
     */
    public function updateQuestion(Request $request, Question $question)
    {
        $this->authorize('update', $question->quiz->lesson->course);

        $validated = $request->validate([
            'content' => 'sometimes|string',
            'type' => 'nullable|string|in:mcq,multiple,short_answer,essay',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable',
            'points' => 'nullable|numeric|min:1',
            'order' => 'nullable|integer',
            'explanation' => 'nullable|string',
        ]);

        $question->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'question' => $question->fresh(),
        ]);
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion(Question $question)
    {
        $this->authorize('update', $question->quiz->lesson->course);

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully',
        ]);
    }

    /**
     * Start a quiz attempt.
     */
    public function startAttempt(Quiz $quiz)
    {
        $user = Auth::user();

        // Check enrollment
        $course = $quiz->lesson->course;
        $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();

        if (!$isEnrolled && $course->instructor_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Not enrolled in this course',
            ], 403);
        }

        try {
            $submission = $this->assessmentService->startAttempt($quiz, $user);

            // Get questions (randomized if configured)
            $questions = $quiz->questions()->orderBy('order');

            if ($quiz->randomize) {
                $questions = $questions->inRandomOrder();
            }

            if ($quiz->questions_per_attempt) {
                $questions = $questions->limit($quiz->questions_per_attempt);
            }

            return response()->json([
                'success' => true,
                'submission' => $submission,
                'questions' => $questions->get()->map(fn($q) => [
                    'id' => $q->id,
                    'content' => $q->content,
                    'type' => $q->type,
                    'options' => $q->options,
                    'points' => $q->points,
                ]),
                'time_limit' => $quiz->time_limit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Submit answers for grading.
     */
    public function submit(Request $request, Submission $submission)
    {
        // Ensure user owns this submission
        if ($submission->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($submission->status !== Submission::STATUS_STARTED) {
            return response()->json([
                'success' => false,
                'message' => 'Submission already completed',
            ], 422);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $submission = $this->assessmentService->submitAnswers($submission, $validated['answers']);
        $quiz = $submission->quiz;

        $response = [
            'success' => true,
            'message' => 'Submission graded successfully',
            'score' => $submission->score,
            'max_score' => $submission->max_score,
            'percentage' => $submission->percentage,
            'passed' => $submission->isPassed(),
            'status' => $submission->status,
        ];

        // Include answers if configured
        if ($quiz->show_answers_after_submit) {
            $response['responses'] = $submission->responses()->with('question')->get();
        }

        return response()->json($response);
    }

    /**
     * Get quiz statistics for instructors.
     */
    public function stats(Quiz $quiz)
    {
        $this->authorize('update', $quiz->lesson->course);

        $stats = $this->assessmentService->getQuizStats($quiz);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Get a student's result.
     */
    public function result(Quiz $quiz)
    {
        $result = $this->assessmentService->getStudentResult($quiz, Auth::user());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No submission found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * Grade an essay question manually.
     */
    public function gradeEssay(Request $request, QuestionResponse $response)
    {
        $this->authorize('update', $response->question->quiz->lesson->course);

        $validated = $request->validate([
            'points' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        $response = $this->assessmentService->gradeEssay(
            $response,
            $validated['points'],
            $validated['feedback'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Essay graded successfully',
            'response' => $response,
        ]);
    }
}
