<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">{{ __('Create New Course') }}</h2>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Simple JS-driven form for nested data. In a real app, use Livewire or React/Vue -->
                <form action="{{ route('admin.courses.store') }}" method="POST" id="courseForm">
                    @csrf

                    <!-- Course Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Course Details') }}
                        </h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="title"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                                <input type="text" name="course[title]" id="title"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                            </div>
                            <div>
                                <label for="description"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea name="course[description]" id="description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required></textarea>
                            </div>

                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="course[published]" value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Publish immediately</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Modules Section -->
                    <div id="modules-container">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            {{ __('Course Curriculum') }}</h3>
                        <!-- Modules will be added here by JS -->
                    </div>

                    <div class="mt-4">
                        <button type="button" onclick="addModule()"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                            + Add Module
                        </button>
                    </div>

                    <div class="mt-8 border-t pt-6">
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let moduleCount = 0;

            function addModule() {
                const container = document.getElementById('modules-container');
                const index = moduleCount++;

                const html = `
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200">Module ${index + 1}</h4>
                        </div>

                        <div class="grid grid-cols-1 gap-4 mb-4">
                            <input type="hidden" name="modules[${index}][order]" value="${index + 1}">
                            <div>
                                <input type="text" name="modules[${index}][title]" placeholder="Module Title" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 shadow-sm text-sm" required>
                            </div>
                            <div>
                                <textarea name="modules[${index}][description]" placeholder="Module Description" rows="2" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 shadow-sm text-sm"></textarea>
                            </div>
                        </div>

                        <!-- Lessons & Assignments Container -->
                        <div class="pl-4 border-l-2 border-indigo-200 space-y-3">
                             <div id="module-${index}-items"></div>

                             <div class="flex gap-2">
                                <button type="button" onclick="addLesson(${index})" class="text-xs text-indigo-600 hover:text-indigo-800">+ Add Lesson</button>
                                <button type="button" onclick="addAssignment(${index})" class="text-xs text-green-600 hover:text-green-800">+ Add Assignment</button>
                            </div>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', html);
            }

            function addLesson(moduleIndex) {
                const container = document.getElementById(`module-${moduleIndex}-items`);
                const count = container.children.length; // Simple count for unique ordering

                const html = `
                    <div class="bg-white dark:bg-gray-800 p-3 rounded shadow-sm mb-2">
                        <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Lesson</p>
                        <input type="hidden" name="modules[${moduleIndex}][lessons][${count}][order]" value="${count + 1}">
                        <div class="grid grid-cols-1 gap-2">
                            <input type="text" name="modules[${moduleIndex}][lessons][${count}][title]" placeholder="Lesson Title" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm" required>
                            <input type="url" name="modules[${moduleIndex}][lessons][${count}][video_url]" placeholder="Video URL (optional)" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            }

            function addAssignment(moduleIndex) {
                const container = document.getElementById(`module-${moduleIndex}-items`);
                const count = container.children.length; // Use same index space or different, here reusing simple counter logic implies 'items' in general
                // Note: In real app, you'd manage indexes more robustly. validating 'modules.*.assignments' array needs specific indexes.
                // Let's use Date.now() for unique keys to avoid collision if mixed
                const key = Date.now();

                const html = `
                    <div class="bg-white dark:bg-gray-800 p-3 rounded shadow-sm border-l-4 border-green-500 mb-2">
                        <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Assignment</p>
                        <div class="grid grid-cols-1 gap-2">
                            <input type="text" name="modules[${moduleIndex}][assignments][${key}][title]" placeholder="Assignment Title" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm" required>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="modules[${moduleIndex}][assignments][${key}][max_score]" placeholder="Max Score" value="100" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                <input type="date" name="modules[${moduleIndex}][assignments][${key}][due_date]" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            }

            // Add one initial module
            addModule();
        </script>
    @endpush
</x-layouts.app>