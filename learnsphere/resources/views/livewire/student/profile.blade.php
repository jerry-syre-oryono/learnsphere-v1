<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                        @if(isset($gradeReport['cgpa']))
                                            {{ number_format($gradeReport['cgpa'], 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">Overall CGPA</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">My Academic Performance</h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($gradeReport['cgpa'], 2) }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">CGPA</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $gradeReport['academic_standing']['status'] ?? 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Academic Standing</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $gradeReport['classification']['classification'] ?? ($gradeReport['classification']['class'] ?? 'N/A') }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Classification</div>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">My Courses</h3>

                        <div class="space-y-4">
                            @forelse($courseProgress as $progress)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ $progress['course']->title }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                Progress: {{ round($progress['progress']) }}%
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    {{ $progress['grade'] ?? 'N/A' }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500">Grade Point: {{ number_format($progress['grade_point'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <p>You are not enrolled in any courses yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Features</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Assignment due dates and reminders</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Direct messaging with instructors</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Detailed analytics and progress charts</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Certificate downloads and achievements</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>