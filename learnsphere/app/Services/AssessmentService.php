<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Quiz;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    /**
     * Create a new quiz/exam.
     */
    public function createQuiz(array $data): Quiz
    {
        return Quiz::create([
            'lesson_id' => $data['lesson_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'quiz',
            'time_limit' => $data['time_limit'] ?? 0,
            'max_attempts' => $data['max_attempts'] ?? 1,
            'randomize' => $data['randomize'] ?? false,
            'questions_per_attempt' => $data['questions_per_attempt'] ?? null,
            'passing_score' => $data['passing_score'] ?? 60.00,
            'weight' => $data['weight'] ?? 1.00,
            'available_from' => $data['available_from'] ?? null,
            'available_until' => $data['available_until'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'show_answers_after_submit' => $data['show_answers_after_submit'] ?? false,
        ]);
    }

    /**
     * Add a question to a quiz.
     */
    public function addQuestion(Quiz $quiz, array $data): Question
    {
        $order = $data['order'] ?? ($quiz->questions()->max('order') + 1);

        return $quiz->questions()->create([
            'content' => $data['content'],
            'type' => $data['type'] ?? Question::TYPE_MCQ,
            'options' => $data['options'] ?? null,
            'correct_answer' => $data['correct_answer'] ?? null,
            'points' => $data['points'] ?? 1,
            'order' => $order,
            'explanation' => $data['explanation'] ?? null,
        ]);
    }

    /**
     * Start a submission attempt for a user.
     */
    public function startAttempt(Quiz $quiz, User $user): Submission
    {
        // Check if user can attempt
        $attemptCount = Submission::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        if ($attemptCount >= $quiz->max_attempts) {
            throw new \Exception("Maximum attempts reached for this quiz.");
        }

        return Submission::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'answers' => [],
            'status' => Submission::STATUS_STARTED,
            'started_at' => now(),
            'attempt_number' => $attemptCount + 1,
        ]);
    }

    /**
     * Submit answers and grade the submission.
     */
    public function submitAnswers(Submission $submission, array $answers): Submission
    {
        return DB::transaction(function () use ($submission, $answers) {
            $quiz = $submission->quiz;
            $totalPoints = 0;
            $earnedPoints = 0;

            foreach ($quiz->questions as $question) {
                $userAnswer = $answers[$question->id] ?? null;
                $totalPoints += $question->points;

                // Auto-grade for supported question types
                $isCorrect = null;
                $pointsEarned = 0;

                if (in_array($question->type, [Question::TYPE_MCQ, Question::TYPE_MULTIPLE])) {
                    $isCorrect = $this->gradeMultipleChoice($question, $userAnswer);
                    $pointsEarned = $isCorrect ? $question->points : 0;
                    $earnedPoints += $pointsEarned;
                } elseif ($question->type === Question::TYPE_SHORT_ANSWER) {
                    $isCorrect = $this->gradeShortAnswer($question, $userAnswer);
                    $pointsEarned = $isCorrect ? $question->points : 0;
                    $earnedPoints += $pointsEarned;
                }
                // Essay questions remain null (manual grading required)

                // Record the response
                QuestionResponse::create([
                    'submission_id' => $submission->id,
                    'question_id' => $question->id,
                    'answer' => $userAnswer,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                    'answered_at' => now(),
                ]);
            }

            // Calculate percentage
            $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

            // Determine status
            $hasEssay = $quiz->questions()->where('type', Question::TYPE_ESSAY)->exists();
            $status = $hasEssay ? Submission::STATUS_PENDING_REVIEW : Submission::STATUS_COMPLETED;

            // Update submission
            $submission->update([
                'answers' => $answers,
                'score' => $earnedPoints,
                'max_score' => $totalPoints,
                'percentage' => $percentage,
                'status' => $status,
                'completed_at' => now(),
            ]);

            return $submission->fresh();
        });
    }

    /**
     * Grade a multiple choice question.
     */
    protected function gradeMultipleChoice(Question $question, $userAnswer): bool
    {
        if ($userAnswer === null) {
            return false;
        }

        $correctAnswer = $question->correct_answer;

        // Handle array answers (multiple correct)
        if (is_array($correctAnswer)) {
            $userAnswerArray = is_array($userAnswer) ? $userAnswer : [$userAnswer];
            sort($correctAnswer);
            sort($userAnswerArray);
            return $correctAnswer === $userAnswerArray;
        }

        return strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
    }

    /**
     * Grade a short answer question (case-insensitive comparison).
     */
    protected function gradeShortAnswer(Question $question, $userAnswer): bool
    {
        if ($userAnswer === null || trim($userAnswer) === '') {
            return false;
        }

        $correctAnswer = $question->correct_answer;

        // Support multiple acceptable answers
        if (is_array($correctAnswer)) {
            return in_array(strtolower(trim($userAnswer)), array_map('strtolower', array_map('trim', $correctAnswer)));
        }

        return strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
    }

    /**
     * Manually grade an essay question.
     */
    public function gradeEssay(QuestionResponse $response, float $points, ?string $feedback = null): QuestionResponse
    {
        $response->update([
            'points_earned' => $points,
            'is_correct' => $points > 0,
            'feedback' => $feedback,
        ]);

        // Recalculate submission totals
        $this->recalculateSubmissionScore($response->submission);

        return $response->fresh();
    }

    /**
     * Recalculate a submission's total score after manual grading.
     */
    protected function recalculateSubmissionScore(Submission $submission): void
    {
        $responses = $submission->responses ?? QuestionResponse::where('submission_id', $submission->id)->get();

        $earnedPoints = $responses->sum('points_earned');
        $maxScore = $submission->max_score;
        $percentage = $maxScore > 0 ? ($earnedPoints / $maxScore) * 100 : 0;

        // Check if all essays are graded
        $ungradedEssays = QuestionResponse::where('submission_id', $submission->id)
            ->whereHas('question', fn($q) => $q->where('type', Question::TYPE_ESSAY))
            ->whereNull('is_correct')
            ->count();

        $status = $ungradedEssays > 0 ? Submission::STATUS_PENDING_REVIEW : Submission::STATUS_COMPLETED;

        $submission->update([
            'score' => $earnedPoints,
            'percentage' => $percentage,
            'status' => $status,
        ]);
    }

    /**
     * Get class statistics for a quiz.
     */
    public function getQuizStats(Quiz $quiz): array
    {
        $submissions = Submission::where('quiz_id', $quiz->id)
            ->where('status', Submission::STATUS_COMPLETED)
            ->get();

        if ($submissions->isEmpty()) {
            return [
                'total_submissions' => 0,
                'average_score' => 0,
                'pass_rate' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
            ];
        }

        $scores = $submissions->pluck('percentage');
        $passingScore = $quiz->passing_score;

        return [
            'total_submissions' => $submissions->count(),
            'average_score' => round($scores->avg(), 2),
            'pass_rate' => round($scores->filter(fn($s) => $s >= $passingScore)->count() / $submissions->count() * 100, 2),
            'highest_score' => round($scores->max(), 2),
            'lowest_score' => round($scores->min(), 2),
        ];
    }

    /**
     * Get a student's result for a quiz.
     */
    public function getStudentResult(Quiz $quiz, User $user): ?array
    {
        $submission = Submission::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$submission) {
            return null;
        }

        $responses = QuestionResponse::where('submission_id', $submission->id)
            ->with('question')
            ->get();

        return [
            'submission' => $submission,
            'responses' => $responses,
            'passed' => $submission->percentage >= $quiz->passing_score,
            'can_retake' => $submission->attempt_number < $quiz->max_attempts,
        ];
    }
}
