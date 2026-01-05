<div>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-4">{{ $lesson->title }}</h1>

    @if($lesson->content_type === 'video' && $lesson->video_url)
        <div class="aspect-video mb-6">
            @php
                $url = $lesson->video_url;
                if (str_contains($url, 'youtube.com/watch?v=')) {
                    $url = str_replace('watch?v=', 'embed/', $url);
                } elseif (str_contains($url, 'youtu.be/')) {
                    $url = str_replace('youtu.be/', 'youtube.com/embed/', $url);
                }
            @endphp
            <iframe class="w-full h-full rounded-lg shadow-lg" src="{{ $url }}" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
        </div>
    @endif

    <div class="prose dark:prose-invert max-w-none mb-6">
        {!! nl2br(e($lesson->content)) !!}
    </div>

    {{-- Single Legacy Attachment --}}
    @if($lesson->attachment_path)
        <div
            class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg flex items-center justify-between mb-6 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-indigo-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <div>
                    <span class="block text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $lesson->attachment_name ?: 'Course Resource' }}
                    </span>
                    <span
                        class="text-xs text-gray-500 font-mono uppercase">{{ pathinfo($lesson->attachment_path, PATHINFO_EXTENSION) }}
                        Document</span>
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('lesson.attachment.stream', $lesson) }}" target="_blank"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    View
                </a>
                <a href="{{ route('lesson.attachment.stream', $lesson) }}?download=1"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                    Download
                </a>
            </div>
        </div>
    @endif

    {{-- New Media Collection --}}
    @if($lesson->media->count() > 0)
        <div class="space-y-3 mb-8">
            <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Supplemental Resources
            </h3>
            <div class="grid grid-cols-1 gap-3">
                @foreach($lesson->media as $media)
                    <div
                        class="bg-white dark:bg-gray-800 p-4 rounded-xl flex items-center justify-between shadow-sm border border-gray-100 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-900 transition-colors">
                        <div class="flex items-center">
                            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg mr-4">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <span
                                    class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $media->title ?: $media->filename }}</span>
                                <span class="text-xs text-gray-500">{{ $media->formatted_size }} •
                                    {{ strtoupper($media->type) }}</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('lesson.media.stream', $media) }}" target="_blank"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold text-xs p-2">
                                VIEW
                            </a>
                            <a href="{{ route('lesson.media.stream', $media) }}?download=1"
                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold text-xs p-2">
                                DOWNLOAD
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="flex items-center space-x-4 mt-8">
        @if(!$isCompleted)
            <button wire:click="markAsComplete"
                class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                Mark as Complete
            </button>
        @else
            <span class="text-green-500 font-semibold flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Completed
            </span>
        @endif

        @if($quiz)
            <a href="{{ route('quiz.take', $quiz) }}"
                class="px-4 py-2 bg-purple-600 text-white font-semibold rounded-md hover:bg-purple-700">
                Start Quiz
            </a>
        @endif
    </div>

    <div class="mt-8">
        <a href="{{ route('course.show', $lesson->course) }}" class="text-blue-500 hover:underline">&larr; Back to
            Course</a>
    </div>
</div>