<div>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-4">Quiz Result: {{ $submission->quiz->title }}</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-6">Your Score: {{ $submission->score }}</p>

    @if($submission->status === \App\Models\Submission::STATUS_PENDING_REVIEW)
        <p class="text-yellow-500 font-semibold">Some questions require manual grading by the instructor.</p>
    @endif

    <div class="mt-8">
        <a href="{{ route('course.show', $submission->quiz->lesson->course) }}" class="text-blue-500 hover:underline">&larr; Back to Course</a>
    </div>
</div>
