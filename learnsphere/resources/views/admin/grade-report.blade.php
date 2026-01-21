<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Grade Report for {{ $course->title }}
                </h2>
                <form method="POST" action="{{ route('admin.courses.process-grades', $course) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Calculate Final Grades
                    </button>
                </form>
            </div>

            @if (session('status'))
                <div class="mb-4 text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            @livewire('admin.grade-book', ['course' => $course])
        </div>
    </div>
</x-layouts.app>
