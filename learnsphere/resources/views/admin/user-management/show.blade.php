<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.user-management.index') }}"
                   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to User Management
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-xl font-medium text-gray-700">
                                    {{ $user->initials() }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                            <p class="text-gray-600 dark:text-gray-300">{{ $user->email }}</p>
                            <p class="text-sm text-gray-500">
                                Student since {{ $user->created_at->format('M j, Y') }}
                                @if($user->enrollments->count() > 0)
                                    • Student Number: {{ $user->enrollments->first()->student_number }}
                                @endif
                            </p>
                        </div>
                        <div class="ml-auto">
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    @if($overallGPA)
                                        {{ number_format($overallGPA, 1) }}%
                                    @else
                                        N/A
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">Overall GPA</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Course Enrollments</h2>

                    <div class="space-y-4">
                        @forelse($courseProgress as $courseData)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $courseData['course']->title }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            Student Number: {{ $courseData['student_number'] }}
                                            @if($courseData['enrollment_year'])
                                                • Enrolled: {{ $courseData['enrollment_year'] }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                            @if($courseData['grade'])
                                                {{ number_format($courseData['grade'], 1) }}%
                                            @else
                                                No grade yet
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">Course Grade</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300 mb-1">
                                        <span>Progress</span>
                                        <span>{{ number_format($courseData['progress'], 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $courseData['progress'] }}%"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Lessons:</span>
                                        <span class="ml-2 font-medium">
                                            {{ $courseData['course']->lessons->count() }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Assignments:</span>
                                        <span class="ml-2 font-medium">
                                            {{ $courseData['course']->assignments->count() }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Completed:</span>
                                        <span class="ml-2 font-medium">
                                            {{ $user->completedLessons()->where('course_id', $courseData['course']->id)->count() }}
                                            / {{ $courseData['course']->lessons->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                This student is not enrolled in any courses.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole('admin'))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Danger Zone</h2>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                            Removing a student will permanently delete their account and all associated data.
                            This action cannot be undone.
                        </p>
                        <form method="POST" action="{{ route('admin.user-management.destroy', $user) }}"
                              onsubmit="return confirm('Are you sure you want to permanently remove this student? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Remove Student
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>