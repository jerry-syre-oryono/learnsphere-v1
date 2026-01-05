<div>
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">My Courses</h2>
        @if($enrolledCourses->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($enrolledCourses as $course)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ Str::limit($course->description, 100) }}</p>
                            <div class="mt-4">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $course->completion_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $course->completion_percentage }}%"></div>
                                </div>
                                <a href="{{ route('course.show', $course) }}" class="mt-4 inline-block text-blue-500 hover:underline">Continue Learning &rarr;</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">You are not enrolled in any courses yet.</p>
        @endif
    </div>

    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Course Catalog</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($courseCatalog as $course)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ Str::limit($course->description, 100) }}</p>
                        <div class="mt-4">
                            <button wire:click="enroll({{ $course->id }})" wire:loading.attr="disabled" class="w-full px-4 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 disabled:opacity-50">
                                Enroll Now
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400">No other courses available at the moment.</p>
            @endforelse
        </div>
    </div>
</div>
