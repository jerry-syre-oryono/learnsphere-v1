<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\Question;

class GradingService
{
    public function grade(Submission $submission): void
    {
        $quiz = $submission->quiz;
        $totalQuestions = $quiz->questions->count();
        $correctAnswers = 0;

        $questions = $quiz->questions->keyBy('id');

        foreach ($submission->answers as $answerData) {
            $questionId = $answerData['question_id'];
            $userAnswer = $answerData['answer'];

            if (!isset($questions[$questionId])) {
                continue;
            }

            $question = $questions[$questionId];

            // Simple string comparison for now, can be expanded for complex types
            if ($this->checkAnswer($question, $userAnswer)) {
                $correctAnswers++;
            }
        }

        $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        $submission->update([
            'score' => $score,
            'status' => Submission::STATUS_COMPLETED, // Or PENDING_REVIEW if essay questions exist
        ]);
    }

    protected function checkAnswer(Question $question, $userAnswer): bool
    {
        // Add logic based on question type
        if ($question->type === Question::TYPE_MCQ || $question->type === Question::TYPE_SHORT_ANSWER) {
            return trim(strtolower($userAnswer)) === trim(strtolower($question->correct_answer));
        }

        // For multiple correct answers (checkboxes)
        if ($question->type === Question::TYPE_MULTIPLE) {
            // Assuming correct_answer is an array or json string in DB, and userAnswer is array
            // This needs adjustment based on how you store correct answers for multiple choice
            // For now, simple equality
            return $userAnswer == $question->correct_answer;
        }

        return false;
    }
}
