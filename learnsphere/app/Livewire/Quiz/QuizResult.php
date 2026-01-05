<?php

namespace App\Livewire\Quiz;

use App\Models\Submission;
use Livewire\Component;

class QuizResult extends Component
{
    public Submission $submission;

    public function mount(Submission $submission): void
    {
        $this->submission = $submission->load(['quiz.questions', 'user']);
    }

    public function render()
    {
        return view('livewire.quiz.quiz-result');
    }
}
