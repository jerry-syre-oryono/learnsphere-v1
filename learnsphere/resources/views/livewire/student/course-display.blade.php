<div>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-4">{{ $course->title }}</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $course->description }}</p>

    <div class="space-y-8">
        @forelse($modules as $module)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">{{ $module->title }}</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $module->description }}</p>

                <ul class="space-y-3">
                    @forelse($module->lessons as $lesson)
                        <li class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                            <div class="flex items-center">
                                @if(in_array($lesson->id, $completedLessonIds))
                                    <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @endif
                                <a href="{{ route('lesson.show', $lesson) }}" class="text-lg font-medium text-gray-800 dark:text-gray-200 hover:text-blue-500">
                                    {{ $lesson->title }}
                                </a>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                @if($lesson->quiz)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Quiz</span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">No lessons in this module yet.</li>
                    @endforelse
                </ul>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400">No modules found for this course.</p>
        @endforelse
    </div>
</div>
