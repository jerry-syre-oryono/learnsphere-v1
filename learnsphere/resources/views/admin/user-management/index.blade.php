<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-4 sm:space-y-0">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                    {{ __('User Management') }}
                </h2>
                <div>
                    <form method="GET" class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0 w-full sm:w-auto">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search students..."
                               class="flex-grow px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <select name="course" class="flex-grow px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course') == $course->id ? 'selected' : '' }}>
                                    {{ $course->title }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <!-- Desktop Table View -->
                <div class="overflow-x-auto hidden md:block">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Enrolled Courses
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Overall Progress
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    GPA
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ $student->initials() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $student->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $student->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        @if($student->enrollments->count() > 0)
                                            {{ $student->enrollments->first()->student_number ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ $student->enrollments->count() }} courses
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            @foreach($student->enrollments->take(2) as $enrollment)
                                                <span class="inline-block bg-gray-100 dark:bg-gray-600 rounded px-2 py-1 mr-1 mb-1">
                                                    {{ Str::limit($enrollment->course->title, 20) }}
                                                </span>
                                            @endforeach
                                            @if($student->enrollments->count() > 2)
                                                <span class="text-xs text-gray-400">+{{ $student->enrollments->count() - 2 }} more</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $totalProgress = $student->course_data->avg('progress');
                                        @endphp
                                        <div class="flex items-center">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $totalProgress }}%"></div>
                                            </div>
                                            <span class="text-sm text-gray-900 dark:text-white">
                                                {{ number_format($totalProgress, 1) }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        @if($student->overall_gpa)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($student->overall_gpa >= 90) bg-green-100 text-green-800
                                                @elseif($student->overall_gpa >= 80) bg-blue-100 text-blue-800
                                                @elseif($student->overall_gpa >= 70) bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ number_format($student->overall_gpa, 1) }}%
                                            </span>
                                        @else
                                            <span class="text-gray-400">No grades yet</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.user-management.show', $student) }}"
                                           class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            View Details
                                        </a>
                                        @if(auth()->user()->hasRole('admin'))
                                            <form method="POST" action="{{ route('admin.user-management.destroy', $student) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to remove this student?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="grid grid-cols-1 gap-4 md:hidden p-4">
                    @forelse($students as $student)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ $student->initials() }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white">
                                        {{ $student->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $student->email }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-200 mb-3">
                                <p><strong>Student No.:</strong> {{ $student->enrollments->first()->student_number ?? 'N/A' }}</p>
                                <p><strong>Courses:</strong> {{ $student->enrollments->count() }}
                                    @if($student->enrollments->count() > 0)
                                        <span class="text-xs text-gray-500">
                                            ({{ $student->enrollments->pluck('course.title')->join(', ') }})
                                        </span>
                                    @endif
                                </p>
                                @php
                                    $totalProgress = $student->course_data->avg('progress');
                                @endphp
                                <p><strong>Progress:</strong> {{ number_format($totalProgress, 1) }}%</p>
                                <p>
                                    <strong>GPA:</strong>
                                    @if($student->overall_gpa)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($student->overall_gpa >= 90) bg-green-100 text-green-800
                                            @elseif($student->overall_gpa >= 80) bg-blue-100 text-blue-800
                                            @elseif($student->overall_gpa >= 70) bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ number_format($student->overall_gpa, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400">No grades yet</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-col space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('admin.user-management.show', $student) }}"
                                   class="w-full text-center px-4 py-2 bg-indigo-100 text-indigo-800 rounded hover:bg-indigo-200 text-sm">
                                    View Details
                                </a>
                                @if(auth()->user()->hasRole('admin'))
                                    <form method="POST" action="{{ route('admin.user-management.destroy', $student) }}"
                                          class="w-full"
                                          onsubmit="return confirm('Are you sure you want to remove this student?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-4 py-2 bg-red-100 text-red-800 rounded hover:bg-red-200 text-sm">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-center text-gray-500">
                            No students found.
                        </div>
                    @endforelse
                </div>

                @if($students->hasPages())
                    <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>