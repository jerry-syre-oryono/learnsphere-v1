<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                    {{ __('Edit Course') }}: {{ $course->title }}
                </h2>
                <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">
                    &larr; Back to Dashboard
                </a>
            </div>

            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Course Editor with Alpine.js --}}
            <div x-data="courseEditor()" x-init="init()" class="space-y-6">

                {{-- Course Details Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Course Details</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="title"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <input type="text" id="title" x-model="course.title"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea id="description" x-model="course.description" rows="3"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="flex items-center space-x-8">
                            <div class="flex items-center space-x-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Course
                                    Visibility</span>
                                <label for="published" class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="published" x-model="course.published"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600">
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-600 dark:text-gray-400"
                                        x-text="course.published ? 'Published' : 'Draft'"></span>
                                </label>
                            </div>

                            <div class="flex-1 max-w-xs">
                                <label for="enrollment_code"
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                                    Secure Enrollment Code
                                </label>
                                <input type="text" id="enrollment_code" x-model="course.enrollment_code"
                                    placeholder="Leave empty for public"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 text-sm font-mono tracking-widest">
                                <p class="text-[10px] text-gray-500 mt-1 italic">Students must enter this to enroll.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Course Curriculum Card with Drag-and-Drop --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Curriculum</h3>
                        <button @click="addModule()" type="button"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm transition">
                            + Add Module
                        </button>
                    </div>

                    {{-- Modules List --}}
                    <div class="space-y-4">
                        <template x-for="(module, moduleIndex) in modules" :key="moduleIndex">
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50 overflow-hidden">

                                {{-- Module Header --}}
                                <div
                                    class="flex items-center justify-between p-4 bg-gray-100 dark:bg-gray-800 cursor-move">
                                    <div class="flex items-center space-x-3 flex-1">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 8h16M4 16h16" />
                                        </svg>
                                        <input type="text" x-model="module.title" placeholder="Module Title"
                                            class="flex-1 bg-transparent border-0 text-lg font-medium text-gray-900 dark:text-white focus:ring-0 p-0">
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button @click="module.expanded = !module.expanded" type="button"
                                            class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition">
                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform"
                                                :class="{'rotate-180': module.expanded}" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button @click="deleteModule(moduleIndex)" type="button"
                                            class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Module Content --}}
                                <div x-show="module.expanded" x-collapse class="p-4 space-y-4">
                                    <div>
                                        <textarea x-model="module.description"
                                            placeholder="Module description (optional)" rows="2"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm"></textarea>
                                    </div>

                                    {{-- Lessons Section --}}
                                    <div class="pl-4 border-l-2 border-indigo-200 dark:border-indigo-800 space-y-3">
                                        <template x-for="(lesson, lessonIndex) in module.lessons" :key="lessonIndex">
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-100 dark:border-gray-700">
                                                <div class="flex items-start justify-between mb-3">
                                                    <div class="flex items-center space-x-2 flex-1">
                                                        <svg class="w-4 h-4 text-gray-400 cursor-move" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M4 8h16M4 16h16" />
                                                        </svg>
                                                        <input type="text" x-model="lesson.title"
                                                            placeholder="Lesson Title"
                                                            class="flex-1 bg-transparent border-0 font-medium text-gray-900 dark:text-white focus:ring-0 p-0">
                                                    </div>
                                                    <button @click="deleteLesson(moduleIndex, lessonIndex)"
                                                        type="button" class="p-1 text-red-500 hover:bg-red-100 rounded">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label
                                                            class="block text-xs font-medium text-gray-500 mb-1">Content
                                                            Type</label>
                                                        <select x-model="lesson.content_type"
                                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                                            <option value="text">Text Content</option>
                                                            <option value="video">Video URL</option>
                                                            <option value="pdf">PDF Document</option>
                                                            <option value="quiz">Quiz</option>
                                                        </select>
                                                    </div>

                                                    <div x-show="lesson.content_type === 'video'">
                                                        <label
                                                            class="block text-xs font-medium text-gray-500 mb-1">Video
                                                            URL</label>
                                                        <input type="url" x-model="lesson.video_url"
                                                            placeholder="YouTube/Vimeo URL"
                                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                                    </div>
                                                </div>

                                                <div x-show="lesson.content_type === 'text'" class="mt-3">
                                                    <textarea x-model="lesson.content" placeholder="Lesson content..."
                                                        rows="3"
                                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                                                </div>

                                                <div x-show="lesson.content_type === 'quiz'" class="mt-3">
                                                    <div x-show="lesson.assessment" class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-lg border border-purple-100 dark:border-purple-800/50">
                                                        <div class="flex justify-between items-center">
                                                            <p class="text-sm font-medium text-purple-800 dark:text-purple-300">
                                                                Quiz Weight: <span x-text="lesson.assessment.weight || 0"></span>%
                                                            </p>
                                                            <button @click="$dispatch('open-quiz-builder', { lessonId: lesson.id, assessment: lesson.assessment })" type="button" class="text-sm text-purple-600 hover:underline">
                                                                Edit Quiz
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div x-show="!lesson.assessment">
                                                        <button @click="$dispatch('open-quiz-builder', { lessonId: lesson.id })" type="button" class="w-full py-2 text-sm text-white bg-purple-600 hover:bg-purple-700 rounded">
                                                            Build Quiz
                                                        </button>
                                                    </div>
                                                </div>

                                                <div x-show="lesson.content_type === 'pdf'" class="mt-3">
                                                    <div @dragover.prevent="lesson.isDragging = true"
                                                        @dragleave.prevent="lesson.isDragging = false"
                                                        @drop.prevent="handleFileDrop($event, moduleIndex, lessonIndex)"
                                                        @click="$refs['fileInput' + moduleIndex + '_' + lessonIndex].click()"
                                                        :class="lesson.isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600'"
                                                        class="flex items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                                                        <div class="text-center">
                                                            <svg :class="lesson.file ? 'text-indigo-500' : 'text-gray-400'"
                                                                class="w-8 h-8 mx-auto mb-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                            </svg>
                                                            <p class="text-xs font-medium"
                                                                :class="lesson.file ? 'text-indigo-600' : 'text-gray-500'">
                                                                <span
                                                                    x-text="lesson.file ? lesson.file.name : 'Drop PDF here or click to upload'"></span>
                                                            </p>
                                                            <p x-show="!lesson.file && lesson.attachment_path"
                                                                class="text-[10px] text-gray-400 mt-1"
                                                                x-text="'Current: ' + lesson.attachment_path.split('/').pop()">
                                                            </p>
                                                        </div>
                                                        <input :x-ref="'fileInput' + moduleIndex + '_' + lessonIndex"
                                                            type="file" class="hidden" accept=".pdf,.doc,.docx"
                                                            @change="handleFileSelect($event, moduleIndex, lessonIndex)">
                                                    </div>

                                                    {{-- New Rename Field --}}
                                                    <div class="mt-3 bg-indigo-50 dark:bg-indigo-900/10 p-3 rounded-lg border border-indigo-100 dark:border-indigo-800/50"
                                                        x-show="lesson.file || lesson.attachment_path">
                                                        <label
                                                            class="block text-[10px] font-bold text-indigo-700 dark:text-indigo-400 mb-1 uppercase tracking-wider">Resource
                                                            Display Name</label>
                                                        <div class="flex items-center space-x-2">
                                                            <input type="text" x-model="lesson.attachment_name"
                                                                placeholder="Enter a friendly name..."
                                                                class="flex-1 rounded-md border-indigo-200 dark:border-indigo-800 dark:bg-gray-800 text-sm focus:ring-indigo-500 focus:border-indigo-500 py-1">
                                                            <button x-show="lesson.file"
                                                                @click.stop="lesson.file = null; $refs['fileInput' + moduleIndex + '_' + lessonIndex].value = ''"
                                                                type="button"
                                                                class="text-xs text-red-500 font-medium px-2 py-1 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                        <p class="text-[10px] text-indigo-500/70 mt-1 italic">This name
                                                            will be visible to students</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <button @click="addLesson(moduleIndex)" type="button"
                                            class="w-full py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded border border-dashed border-indigo-300 dark:border-indigo-700 transition">
                                            + Add Lesson
                                        </button>
                                    </div>

                                    {{-- Assignments Section --}}
                                    <div class="mt-4 pl-4 border-l-2 border-green-200 dark:border-green-800">
                                        <p class="text-sm font-medium text-green-700 dark:text-green-400 mb-2">
                                            Assignments</p>
                                        <template x-for="(assignment, aIndex) in module.assignments || []"
                                            :key="aIndex">
                                            <div
                                                class="bg-green-50 dark:bg-green-900/20 rounded p-4 mb-4 border border-green-100 dark:border-green-800">
                                                <div class="flex items-start justify-between mb-3">
                                                    <div class="flex-1">
                                                        <input type="text" x-model="assignment.title"
                                                            placeholder="Assignment Title"
                                                            class="w-full bg-transparent border-0 font-medium text-gray-900 dark:text-white focus:ring-0 p-0 text-base">
                                                    </div>
                                                    <button @click="module.assignments.splice(aIndex, 1)" type="button"
                                                        class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="mt-3">
                                                    <textarea x-model="assignment.description" placeholder="Assignment description..." rows="3"
                                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"></textarea>
                                                </div>

                                                <div class="grid grid-cols-3 gap-3 mb-4">
                                                    <div>
                                                        <label
                                                            class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Max
                                                            Score</label>
                                                        <input type="number" x-model="assignment.max_score"
                                                            placeholder="Max Score"
                                                            class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-1">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Due
                                                            Date</label>
                                                        <input type="date" x-model="assignment.due_date"
                                                            class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-1">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Weight
                                                            %</label>
                                                        <input type="number" x-model.number="assignment.weight"
                                                            placeholder="Weight %"
                                                            class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-1">
                                                    </div>
                                                </div>

                                                {{-- Assignment File Upload --}}
                                                <div>
                                                    <label
                                                        class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Assignment
                                                        Sheet (PDF/DOC)</label>
                                                    <div @dragover.prevent="assignment.isDragging = true"
                                                        @dragleave.prevent="assignment.isDragging = false"
                                                        @drop.prevent="handleAssignmentFileDrop($event, moduleIndex, aIndex)"
                                                        @click="$refs['assignInput' + moduleIndex + '_' + aIndex].click()"
                                                        :class="assignment.isDragging ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600'"
                                                        class="flex items-center justify-center h-16 border border-dashed rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                                                        <div class="text-center">
                                                            <svg :class="assignment.file ? 'text-green-500' : 'text-gray-400'"
                                                                class="w-5 h-5 mx-auto" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                            </svg>
                                                            <p class="text-[10px] mt-1"
                                                                :class="assignment.file ? 'text-green-600 font-medium' : 'text-gray-500'">
                                                                <span
                                                                    x-text="assignment.file ? assignment.file.name : 'Drop file or click'"></span>
                                                            </p>
                                                        </div>
                                                        <input :x-ref="'assignInput' + moduleIndex + '_' + aIndex"
                                                            type="file" class="hidden" accept=".pdf,.doc,.docx"
                                                            @change="handleAssignmentFileSelect($event, moduleIndex, aIndex)">
                                                    </div>

                                                    {{-- Assignment Rename Field --}}
                                                    <div class="mt-2 bg-green-100/30 dark:bg-green-900/10 p-2 rounded"
                                                        x-show="assignment.file || assignment.attachment_path">
                                                        <input type="text" x-model="assignment.attachment_name"
                                                            placeholder="Friendly name for the sheet..."
                                                            class="w-full bg-transparent border-0 border-b border-green-200 dark:border-green-800 text-xs focus:ring-0 p-0 mb-1 italic">
                                                        <p class="text-[9px] text-green-600/60"
                                                            x-show="!assignment.file && assignment.attachment_path"
                                                            x-text="'Current: ' + assignment.attachment_path.split('/').pop()">
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <button @click="addAssignment(moduleIndex)" type="button"
                                            class="text-sm font-semibold text-green-600 dark:text-green-400 hover:text-green-700 transition">
                                            + Add Assignment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Empty State --}}
                        <div x-show="modules.length === 0" class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p>No modules yet. Click "Add Module" to get started.</p>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm font-medium" :class="{ 'text-red-500': totalWeight !== 100, 'text-green-600': totalWeight === 100 }">
                            Total Weight: <span x-text="totalWeight"></span>%
                        </p>
                        <p x-show="totalWeight !== 100" class="text-xs text-red-500">Total weight must be exactly 100% to save.</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}"
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button @click="save()" type="button" :disabled="saving || totalWeight !== 100"
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center space-x-2 transition">
                        <svg x-show="saving" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span x-text="saving ? 'Saving...' : 'Save Course'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- Quiz Builder Modal --}}
    <div x-data="{ show: false, lessonId: null, assessment: null }"
        @open-quiz-builder.window="show = true; lessonId = $event.detail.lessonId; assessment = $event.detail.assessment; $nextTick(() => { $refs.quizBuilder.initBuilder(lessonId, assessment); })"
        @keydown.escape.window="show = false" x-show="show" class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="show" @click="show = false" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
            </div>

            <div x-show="show" @click.away="show = false" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-gray-100 dark:bg-gray-900 rounded-lg shadow-xl transform transition-all sm:w-full sm:max-w-4xl">
                <div class="p-6">
                    <div @assessment-saved.window="
                        const savedQuiz = $event.detail;
                        const moduleIndex = modules.findIndex(m => m.lessons.some(l => l.id === savedQuiz.lesson_id));
                        if (moduleIndex > -1) {
                            const lessonIndex = modules[moduleIndex].lessons.findIndex(l => l.id === savedQuiz.lesson_id);
                            if (lessonIndex > -1) {
                                modules[moduleIndex].lessons[lessonIndex].assessment = savedQuiz;
                            }
                        }
                        show = false;
                    ">
                        <x-quiz-builder />
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function courseEditor() {
                return {
                    course: {
                        id: {{ $course->id }},
                        title: @json($course->title),
                        description: @json($course->description),
                        published: {{ $course->published ? 'true' : 'false' }},
                        enrollment_code: @json($course->enrollment_code)
                    },
                    modules: @json($modulesData).map(m => ({
                        ...m,
                        lessons: m.lessons.map(l => ({
                            ...l,
                            file: null,
                            isDragging: false,
                            attachment_name: l.attachment_name || ''
                        })),
                        assignments: (m.assignments || []).map(a => ({
                            ...a,
                            file: null,
                            isDragging: false,
                            attachment_name: a.attachment_name || ''
                        }))
                    })),

                    saving: false,

                    get totalWeight() {
                        let total = 0;
                        this.modules.forEach(module => {
                            (module.assignments || []).forEach(assignment => {
                                total += parseFloat(assignment.weight) || 0;
                            });
                            (module.lessons || []).forEach(lesson => {
                                if (lesson.assessment) {
                                    total += parseFloat(lesson.assessment.weight) || 0;
                                }
                            });
                        });
                        return total;
                    },

                    init() {
                        if (this.modules.length === 0) {
                            this.addModule();
                        }
                    },

                    addModule() {
                        this.modules.push({
                            id: null,
                            title: '',
                            description: '',
                            order: this.modules.length + 1,
                            expanded: true,
                            lessons: [],
                            assignments: []
                        });
                    },

                    deleteModule(index) {
                        if (confirm('Delete this module and all its content?')) {
                            this.modules.splice(index, 1);
                        }
                    },

                    addLesson(moduleIndex) {
                        this.modules[moduleIndex].lessons.push({
                            id: null,
                            title: '',
                            content: '',
                            content_type: 'text',
                            video_url: '',
                            order: this.modules[moduleIndex].lessons.length + 1,
                            attachment_path: null,
                            attachment_name: '',
                            file: null,
                            isDragging: false,
                            assessment: null
                        });
                    },

                    deleteLesson(moduleIndex, lessonIndex) {
                        this.modules[moduleIndex].lessons.splice(lessonIndex, 1);
                    },

                    handleFileSelect(event, mIndex, lIndex) {
                        const file = event.target.files[0];
                        if (file) {
                            this.modules[mIndex].lessons[lIndex].file = file;
                        }
                    },

                    handleFileDrop(event, mIndex, lIndex) {
                        this.modules[mIndex].lessons[lIndex].isDragging = false;
                        const file = event.dataTransfer.files[0];
                        if (file) {
                            this.modules[mIndex].lessons[lIndex].file = file;
                        }
                    },

                    addAssignment(moduleIndex) {
                        if (!this.modules[moduleIndex].assignments) {
                            this.modules[moduleIndex].assignments = [];
                        }
                        this.modules[moduleIndex].assignments.push({
                            id: null,
                            title: '',
                            description: '',
                            max_score: 100,
                            due_date: null,
                            weight: 0,
                            attachment_path: null,
                            attachment_name: '',
                            file: null,
                            isDragging: false
                        });
                    },

                    handleAssignmentFileSelect(event, mIndex, aIndex) {
                        const file = event.target.files[0];
                        if (file) {
                            this.modules[mIndex].assignments[aIndex].file = file;
                        }
                    },

                    handleAssignmentFileDrop(event, mIndex, aIndex) {
                        this.modules[mIndex].assignments[aIndex].isDragging = false;
                        const file = event.dataTransfer.files[0];
                        if (file) {
                            this.modules[mIndex].assignments[aIndex].file = file;
                        }
                    },

                    async save() {
                        if (this.totalWeight !== 100) {
                            alert('Total weight of all assessments and assignments must be exactly 100%.');
                            return;
                        }

                        this.saving = true;

                        // Prepare form data
                        const formData = new FormData();
                        formData.append('_method', 'PUT');
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                        formData.append('course[title]', this.course.title || '');
                        formData.append('course[description]', this.course.description || '');
                        formData.append('course[published]', this.course.published ? 1 : 0);
                        formData.append('course[enrollment_code]', this.course.enrollment_code || '');

                        this.modules.forEach((module, mIndex) => {
                            if (module.id) formData.append(`modules[${mIndex}][id]`, module.id);
                            formData.append(`modules[${mIndex}][title]`, module.title || '');
                            formData.append(`modules[${mIndex}][description]`, module.description || '');
                            formData.append(`modules[${mIndex}][order]`, mIndex + 1);

                            (module.lessons || []).forEach((lesson, lIndex) => {
                                if (lesson.id) formData.append(`modules[${mIndex}][lessons][${lIndex}][id]`, lesson.id);
                                formData.append(`modules[${mIndex}][lessons][${lIndex}][title]`, lesson.title || '');
                                formData.append(`modules[${mIndex}][lessons][${lIndex}][content]`, lesson.content || '');
                                formData.append(`modules[${mIndex}][lessons][${lIndex}][content_type]`, lesson.content_type || 'text');
                                formData.append(`modules[${mIndex}][lessons][${lIndex}][video_url]`, lesson.video_url || '');
                                formData.append(`modules[${mIndex}][lessons][${lIndex}][order]`, lIndex + 1);
                                formData.append(`modules[${mIndex}][lessons][${lIndex}][attachment_name]`, lesson.attachment_name || '');
                                if (lesson.file) {
                                    formData.append(`modules[${mIndex}][lessons][${lIndex}][attachment]`, lesson.file);
                                }
                                if (lesson.assessment) {
                                     formData.append(`modules[${mIndex}][lessons][${lIndex}][assessment_weight]`, lesson.assessment.weight || 0);
                                }
                            });

                            (module.assignments || []).forEach((assignment, aIndex) => {
                                if (assignment.id) formData.append(`modules[${mIndex}][assignments][${aIndex}][id]`, assignment.id);
                                formData.append(`modules[${mIndex}][assignments][${aIndex}][title]`, assignment.title || '');
                                formData.append(`modules[${mIndex}][assignments][${aIndex}][description]`, assignment.description || '');
                                formData.append(`modules[${mIndex}][assignments][${aIndex}][max_score]`, assignment.max_score || 100);
                                if (assignment.due_date) formData.append(`modules[${mIndex}][assignments][${aIndex}][due_date]`, assignment.due_date);
                                formData.append(`modules[${mIndex}][assignments][${aIndex}][weight]`, assignment.weight || 0);
                                formData.append(`modules[${mIndex}][assignments][${aIndex}][attachment_name]`, assignment.attachment_name || '');
                                if (assignment.file) {
                                    formData.append(`modules[${mIndex}][assignments][${aIndex}][attachment]`, assignment.file);
                                }
                            });
                        });

                        try {
                            const response = await fetch(`/admin/courses/${this.course.id}`, {
                                method: 'POST',
                                body: formData
                            });

                            if (response.ok) {
                                window.location.href = '/admin/dashboard';
                            } else {
                                const text = await response.text();
                                console.error('Save failed:', text);
                                alert('Save failed. Check console for details.');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Error saving course: ' + error.message);
                        } finally {
                            this.saving = false;
                        }
                    }
                }
            }
        </script>
    @endpush
</x-layouts.app>
