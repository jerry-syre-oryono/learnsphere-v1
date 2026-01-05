{{-- Course Editor Component with Drag-and-Drop --}}
<div x-data="courseEditor()" x-init="init()" class="space-y-6">
    {{-- Module List with Drag and Drop --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Curriculum</h3>
            <button @click="addModule()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                + Add Module
            </button>
        </div>

        <div id="modules-container" x-ref="modulesContainer" class="space-y-4"
            x-on:drop.prevent="handleModuleDrop($event)" x-on:dragover.prevent>
            <template x-for="(module, moduleIndex) in modules" :key="module.id || moduleIndex">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50"
                    :data-module-id="module.id" draggable="true" @dragstart="dragStart($event, 'module', moduleIndex)"
                    @dragend="dragEnd($event)">

                    {{-- Module Header --}}
                    <div
                        class="flex items-center justify-between p-4 cursor-move bg-gray-100 dark:bg-gray-800 rounded-t-lg">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 8h16M4 16h16" />
                            </svg>
                            <input type="text" x-model="module.title" placeholder="Module Title"
                                class="bg-transparent border-0 text-lg font-medium text-gray-900 dark:text-white focus:ring-0 w-full">
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="module.expanded = !module.expanded"
                                class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                                <svg class="w-5 h-5" :class="{'rotate-180': module.expanded}" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <button @click="deleteModule(moduleIndex)"
                                class="p-1 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Module Content (Lessons) --}}
                    <div x-show="module.expanded" x-collapse class="p-4 space-y-3">
                        <textarea x-model="module.description" placeholder="Module description (optional)" rows="2"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"></textarea>

                        {{-- Lessons List --}}
                        <div class="pl-4 border-l-2 border-indigo-200 dark:border-indigo-800 space-y-2"
                            @drop.stop.prevent="handleLessonDrop($event, moduleIndex)" @dragover.prevent>
                            <template x-for="(lesson, lessonIndex) in module.lessons" :key="lesson.id || lessonIndex">
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-3 border border-gray-100 dark:border-gray-700"
                                    :data-lesson-id="lesson.id" draggable="true"
                                    @dragstart.stop="dragStart($event, 'lesson', lessonIndex, moduleIndex)"
                                    @dragend="dragEnd($event)">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center space-x-2 flex-1">
                                            <svg class="w-4 h-4 text-gray-400 cursor-move" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 8h16M4 16h16" />
                                            </svg>
                                            <input type="text" x-model="lesson.title" placeholder="Lesson Title"
                                                class="bg-transparent border-0 text-sm font-medium text-gray-900 dark:text-white focus:ring-0 flex-1">
                                        </div>
                                        <button @click="deleteLesson(moduleIndex, lessonIndex)"
                                            class="p-1 text-red-500 hover:bg-red-100 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Lesson Content Type Selector --}}
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        <select x-model="lesson.content_type"
                                            class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                            <option value="text">Text Content</option>
                                            <option value="video">Video URL</option>
                                            <option value="pdf">PDF Document</option>
                                        </select>

                                        <template x-if="lesson.content_type === 'video'">
                                            <input type="url" x-model="lesson.video_url" placeholder="YouTube/Vimeo URL"
                                                class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        </template>
                                    </div>

                                    <template x-if="lesson.content_type === 'text'">
                                        <textarea x-model="lesson.content" placeholder="Lesson content..." rows="3"
                                            class="mt-2 w-full text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"></textarea>
                                    </template>

                                    {{-- File Upload for PDF/Doc --}}
                                    <template x-if="lesson.content_type === 'pdf'">
                                        <div class="mt-2">
                                            <label
                                                class="flex items-center justify-center w-full h-20 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <div class="text-center">
                                                    <svg class="w-6 h-6 mx-auto text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    <p class="text-xs text-gray-500">Click to upload PDF</p>
                                                </div>
                                                <input type="file" class="hidden"
                                                    @change="handleFileUpload($event, moduleIndex, lessonIndex)"
                                                    accept=".pdf,.doc,.docx">
                                            </label>
                                            <template x-if="lesson.attachment_path">
                                                <p class="text-xs text-green-600 mt-1">File uploaded: <span
                                                        x-text="lesson.attachment_path"></span></p>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Lesson Media Gallery --}}
                                    <template x-if="lesson.media && lesson.media.length > 0">
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <template x-for="file in lesson.media" :key="file.id">
                                                <div class="relative group">
                                                    <template x-if="file.type === 'image'">
                                                        <img :src="file.url"
                                                            class="w-16 h-16 object-cover rounded border">
                                                    </template>
                                                    <template x-if="file.type !== 'image'">
                                                        <div
                                                            class="w-16 h-16 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded border">
                                                            <svg class="w-8 h-8 text-gray-400" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <button @click="deleteMedia(file.id)"
                                                        class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition">×</button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <button @click="addLesson(moduleIndex)"
                                class="w-full py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded border border-dashed border-indigo-300 dark:border-indigo-700">
                                + Add Lesson
                            </button>
                        </div>

                        {{-- Add Assessment Button --}}
                        <button @click="showAssessmentModal(moduleIndex)"
                            class="mt-2 text-sm text-green-600 dark:text-green-400 hover:underline">
                            + Add Quiz/Exam
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <template x-if="modules.length === 0">
            <div class="text-center py-12 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>No modules yet. Click "Add Module" to get started.</p>
            </div>
        </template>
    </div>

    {{-- Save Button --}}
    <div class="flex justify-end">
        <button @click="save()" :disabled="saving"
            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center space-x-2">
            <template x-if="saving">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </template>
            <span x-text="saving ? 'Saving...' : 'Save Course'"></span>
        </button>
    </div>
</div>

<script>
    function courseEditor() {
        return {
            modules: @json($course->modules ?? []),
            courseId: {{ $course->id ?? 'null' }},
            saving: false,
            draggedItem: null,
            draggedType: null,
            draggedModuleIndex: null,

            init() {
                // Ensure each module has an expanded state and lessons array
                this.modules = this.modules.map(m => ({
                    ...m,
                    expanded: true,
                    lessons: m.lessons || []
                }));
            },

            addModule() {
                this.modules.push({
                    id: null,
                    title: '',
                    description: '',
                    order: this.modules.length + 1,
                    expanded: true,
                    lessons: []
                });
            },

            deleteModule(index) {
                if (confirm('Delete this module and all its lessons?')) {
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
                    media: []
                });
            },

            deleteLesson(moduleIndex, lessonIndex) {
                this.modules[moduleIndex].lessons.splice(lessonIndex, 1);
            },

            dragStart(event, type, index, parentIndex = null) {
                this.draggedType = type;
                this.draggedItem = index;
                this.draggedModuleIndex = parentIndex;
                event.dataTransfer.effectAllowed = 'move';
                event.target.classList.add('opacity-50');
            },

            dragEnd(event) {
                event.target.classList.remove('opacity-50');
                this.draggedItem = null;
                this.draggedType = null;
                this.draggedModuleIndex = null;
            },

            handleModuleDrop(event) {
                if (this.draggedType !== 'module') return;
                // Implement reorder logic based on drop position
                // This is a simplified version
            },

            handleLessonDrop(event, targetModuleIndex) {
                if (this.draggedType !== 'lesson') return;
                // Move lesson between modules or reorder within module
            },

            async handleFileUpload(event, moduleIndex, lessonIndex) {
                const file = event.target.files[0];
                if (!file) return;

                const lesson = this.modules[moduleIndex].lessons[lessonIndex];
                if (!lesson.id) {
                    alert('Please save the course first before uploading files.');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch(`/api/lessons/${lesson.id}/upload`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        lesson.media = lesson.media || [];
                        lesson.media.push(data.media);
                    } else {
                        alert(data.message || 'Upload failed');
                    }
                } catch (error) {
                    alert('Upload error: ' + error.message);
                }
            },

            async deleteMedia(mediaId) {
                if (!confirm('Delete this file?')) return;

                try {
                    const response = await fetch(`/api/media/${mediaId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        // Remove from local state
                        this.modules.forEach(m => {
                            m.lessons.forEach(l => {
                                l.media = (l.media || []).filter(f => f.id !== mediaId);
                            });
                        });
                    }
                } catch (error) {
                    alert('Delete error: ' + error.message);
                }
            },

            showAssessmentModal(moduleIndex) {
                // Implement assessment modal
                alert('Assessment builder coming soon!');
            },

            async save() {
                this.saving = true;
                try {
                    // Prepare data
                    const data = {
                        course: {
                            title: document.getElementById('title')?.value,
                            description: document.getElementById('description')?.value,
                            published: document.getElementById('published')?.checked ? 1 : 0
                        },
                        modules: this.modules.map((m, i) => ({
                            ...m,
                            order: i + 1,
                            lessons: m.lessons.map((l, j) => ({
                                ...l,
                                order: j + 1
                            }))
                        }))
                    };

                    const url = this.courseId
                        ? `/admin/courses/${this.courseId}`
                        : '/admin/courses';

                    const method = this.courseId ? 'PUT' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(data)
                    });

                    if (response.ok) {
                        window.location.href = '/admin/dashboard';
                    } else {
                        const result = await response.json();
                        alert('Save failed: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    alert('Save error: ' + error.message);
                } finally {
                    this.saving = false;
                }
            }
        }
    }
</script>