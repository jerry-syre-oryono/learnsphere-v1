<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Results</h1>
                    <p class="text-gray-600 dark:text-gray-300">A detailed breakdown of your grades and scores for each course.</p>
                </div>

                <div class="px-6 py-4">
                    @if (empty($courseData))
                        <div class="text-center py-8 text-gray-500">
                            <p>No course results are available yet.</p>
                        </div>
                    @else
                        <div class="space-y-8">
                            @foreach ($courseData as $data)
                                @php
                                    $report = $data['report'];
                                    $assessableItems = $data['assessableItems'];
                                    $submissions = $data['submissions'];
                                @endphp
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg">
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-t-lg">
                                                                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-300">{{ $report['enrollment']->course->title }}</h2>                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Instructor: {{ $report['enrollment']->course->instructor->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="p-4">
                                        <div class="mb-4">
                                            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Final Grade:
                                                <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-lg font-bold {{ $report['course_results']->first() ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200' }}">
                                                    {{ $report['course_results']->first()->letter_grade ?? 'Pending' }}
                                                </span>
                                            </h3>
                                            <p class="text-sm text-gray-500">Semester GPA: {{ number_format($report['gpa'], 2) }}</p>
                                        </div>

                                        <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Detailed Scores:</h4>
                                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @forelse ($assessableItems as $item)
                                                @php
                                                    $submission = $submissions->get($item->id);
                                                @endphp
                                                <li class="py-2 flex justify-between items-center">
                                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $item->title }} (Weight: {{ $item->weight }}%)</span>
                                                    <span class="text-gray-600 dark:text-gray-400">
                                                        @if($submission && !is_null($submission->score))
                                                            {{ number_format($submission->score, 2) }}%
                                                        @else
                                                            Pending
                                                        @endif
                                                    </span>
                                                </li>
                                            @empty
                                                <li class="py-2 text-center text-gray-500">
                                                    No assessable items for this course.
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
