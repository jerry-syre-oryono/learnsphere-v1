<div>
    <h1>{{ $quiz->title }}</h1>
    <p>{{ $quiz->description }}</p>

    @if($quiz->time_limit > 0)
        <div x-data="{
            timeLeft: {{ $quiz->time_limit * 60 }},
            interval: null,
            init() {
                this.interval = setInterval(() => {
                    this.timeLeft--;
                    if (this.timeLeft <= 0) {
                        clearInterval(this.interval);
                        $wire.submitQuiz();
                    }
                }, 1000);
            },
            formatTime() {
                const minutes = Math.floor(this.timeLeft / 60);
                const seconds = this.timeLeft % 60;
                return `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }" x-init="init()">
            <p>Time Left: <span x-text="formatTime()"></span></p>
        </div>
    @endif

    <form wire:submit.prevent="submitQuiz">
        @foreach($questions as $question)
            <div class="my-4">
                <p class="font-bold">{{ $loop->iteration }}. {{ $question->content }}</p>
                
                @if($question->type === 'mcq')
                    @foreach($question->options as $index => $option)
                        <label>
                            <input type="radio" wire:model.lazy="userAnswers.{{ $question->id }}" value="{{ $index }}">
                            {{ $option['text'] }}
                        </label><br>
                    @endforeach
                @elseif($question->type === 'multiple')
                    @foreach($question->options as $index => $option)
                        <label>
                            <input type="checkbox" wire:model.lazy="userAnswers.{{ $question->id }}" value="{{ $index }}">
                            {{ $option['text'] }}
                        </label><br>
                    @endforeach
                @elseif($question->type === 'short_answer')
                    <input type="text" wire:model.lazy="userAnswers.{{ $question->id }}" class="border-gray-300 rounded-md">
                @elseif($question->type === 'essay')
                    <textarea wire:model.lazy="userAnswers.{{ $question->id }}" class="border-gray-300 rounded-md w-full"></textarea>
                @endif
            </div>
        @endforeach

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Submit Quiz</button>
    </form>
</div>
