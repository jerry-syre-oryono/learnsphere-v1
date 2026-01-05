<?php

use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'totalUsers' => \App\Models\User::count(),
            'totalCourses' => \App\Models\Course::count(),
            'publishedCourses' => \App\Models\Course::published()->count(),
            'draftCourses' => \App\Models\Course::where('published', false)->count(),
            'pendingApprovals' => \App\Models\User::where('is_approved', false)->count(),
            'recentCourses' => \App\Models\Course::latest()->take(10)->get(),
        ];
    }
}; ?>

<div>
    <div class="mb-12">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Management Dashboard</h2>
            <a href="{{ route('admin.courses.create') }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                + Create Course
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-4 border-b-4 border-indigo-500">
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Users</h3>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ $totalUsers }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-4 border-b-4 border-green-500">
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Published</h3>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ $publishedCourses }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-4 border-b-4 border-gray-400">
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Drafts</h3>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ $draftCourses }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-4 border-b-4 border-yellow-500">
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pending</h3>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ $pendingApprovals }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-4 border-b-4 border-blue-500">
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Items</h3>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ $totalCourses }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Courses Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Recently Created Courses</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Course Title</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Created</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCourses as $course)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $course->title }}
                            </td>
                            <td class="px-6 py-4">
                                @if($course->published)
                                    <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Published</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $course->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.courses.edit', $course) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No courses created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>