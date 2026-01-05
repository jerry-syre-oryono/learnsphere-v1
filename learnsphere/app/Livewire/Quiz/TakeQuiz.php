<?php

namespace App\Livewire\Quiz;

use App\Models\Quiz;
use App\Models\Submission;
use App\Services\GradingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TakeQuiz extends Component
{
    public Quiz $quiz;
    public $questions;
    public array $userAnswers = [];
    public ?Submission $submission = null;

    protected ProgressService $progressService;

    public function boot(GradingService $gradingService): void
    {
        $this->gradingService = $gradingService;
    }

    public function mount(Quiz $quiz)
    {
        $this->quiz = $quiz;
        $this->questions = $quiz->randomize ? $quiz->questions->shuffle() : $quiz->questions;

        $this->submission = Submission::firstOrCreate(
            ['user_id' => Auth::id(), 'quiz_id' => $this->quiz->id],
            ['answers' => [], 'status' => Submission::STATUS_STARTED]
        );

        // Load existing answers if submission already exists
        if ($this->submission->answers) {
            foreach ($this->submission->answers as $answer) {
                $this->userAnswers[$answer['question_id']] = $answer['answer'];
            }
        }
    }

    public function updatedUserAnswers()
    {
        $this->saveProgress();
    }

    public function saveProgress()
    {
        $answersForStorage = [];
        foreach ($this->userAnswers as $questionId => $answer) {
            $answersForStorage[] = ['question_id' => $questionId, 'answer' => $answer];
        }

        $this->submission->answers = $answersForStorage;
        $this->submission->save();
    }

    public function submitQuiz()
    {
        $this->saveProgress();
        $this->gradingService->grade($this->submission);
        
        // Redirect to a results page
        return redirect()->route('quiz.result', $this->submission->id);
    }

    public function render()
    {
        return view('livewire.quiz.take-quiz');
    }
}
