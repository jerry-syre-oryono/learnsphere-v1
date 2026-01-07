<div>
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">{{ $course->title }}</h1>
            <p class="text-gray-600 dark:text-gray-400 max-w-2xl">{{ $course->description }}</p>
        </div>
        <div class="flex gap-3">
             @can('update', $course)
                <a href="{{ route('admin.courses.edit', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Modify Course
                </a>
            @endcan

            @if(auth()->user()->hasRole('student'))
                @if($this->isEnrolled)
                    <button disabled class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-default">
                        Enrolled
                    </button>
                @else
                    <button wire:click="enroll" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Enroll Now
                    </button>
                @endif
            @endif
        </div>
    </div>

    @if ($this->isEnrolled)
        <div class="mb-8">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Your Progress</h3>
            <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
                <div class="bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full" style="width: {{ $progress }}%">
                    {{ $progress }}%
                </div>
            </div>
        </div>
    @endif

    {{-- Student Management (Instructor/Admin Only) --}}
    @if(Auth::user()->id === $course->instructor_id || Auth::user()->hasRole('admin'))
        <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-xl p-6 mb-8">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Enrolled Students ({{ $course->students()->count() }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($course->students as $student)
                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-xs mr-3">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $student->name }}</p>
                                <p class="text-[10px] text-gray-500">{{ $student->email }}</p>
                            </div>
                        </div>
                        <button
                            wire:click="removeStudent({{ $student->id }})"
                            wire:confirm="Are you sure you want to remove this student? They will lose all progress."
                            class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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
                                @if($this->isEnrolled || Auth::user()->id === $course->instructor_id || Auth::user()->hasRole('admin'))
                                    <a href="{{ route('lesson.show', $lesson) }}" class="text-lg font-medium text-gray-800 dark:text-gray-200 hover:text-blue-500">
                                        {{ $lesson->title }}
                                    </a>
                                @else
                                    <span class="text-lg font-medium text-gray-400">
                                        {{ $lesson->title }}
                                        <span class="text-[10px] ml-2 text-gray-500 italic font-normal">(Enroll to unlock)</span>
                                    </span>
                                @endif
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

                @if($module->assignments->count() > 0)
                    <div class="mt-6">
                        <h3 class="text-sm font-bold text-green-600 dark:text-green-400 uppercase tracking-widest mb-3">Assignments</h3>
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($module->assignments as $assignment)
                                <div class="bg-green-50/50 dark:bg-green-900/10 border border-green-100 dark:border-green-800 rounded-lg p-3 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-md mr-3 text-green-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $assignment->title }}</span>
                                            @if($assignment->due_date)
                                                <span class="text-[10px] text-gray-500">Due: {{ $assignment->due_date->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($assignment->attachment_path)
                                            @if($this->isEnrolled || Auth::user()->id === $course->instructor_id || Auth::user()->hasRole('admin'))
                                                <a href="{{ route('assignment.stream', $assignment) }}" target="_blank" class="text-xs font-bold text-green-600 hover:text-green-800 uppercase">
                                                    {{ $assignment->attachment_name ?: 'View Details' }}
                                                </a>
                                            @else
                                                <span class="text-[10px] text-gray-400 italic">Enroll to view files</span>
                                            @endif
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">No details attached</span>
                                        @endif
                                    </div>
                                </div>
                                @if($this->isEnrolled && auth()->user()->hasRole('student'))
                                    <div class="mt-2 px-3">
                                        <form method="POST" action="{{ route('assignment.submit', $assignment) }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="flex items-center space-x-2">
                                                <input type="file" name="attachment" required class="text-xs" />
                                                <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-xs rounded">Upload Answer</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400">No modules found for this course.</p>
        @endforelse
    </div>
</div>
