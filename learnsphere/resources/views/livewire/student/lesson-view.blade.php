<div>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-4">{{ $lesson->title }}</h1>
    
    <div class="prose dark:prose-invert mb-6">
        {!! nl2br(e($lesson->content)) !!}
    </div>

    <div class="flex items-center space-x-4 mt-8">
        @if(!$isCompleted)
            <button wire:click="markAsComplete" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                Mark as Complete
            </button>
        @else
            <span class="text-green-500 font-semibold flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Completed
            </span>
        @endif

        @if($quiz)
            <a href="{{ route('quiz.take', $quiz) }}" class="px-4 py-2 bg-purple-600 text-white font-semibold rounded-md hover:bg-purple-700">
                Start Quiz
            </a>
        @endif
    </div>

    <div class="mt-8">
        <a href="{{ route('course.show', $lesson->course) }}" class="text-blue-500 hover:underline">&larr; Back to Course</a>
    </div>
</div>
