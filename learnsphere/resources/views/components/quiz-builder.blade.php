@props(['lessonId' => null])
{{-- Quiz/Exam Builder Component --}}
<div x-data="quizBuilder()" x-ref="quizBuilder" class="space-y-6">
    {{-- Quiz Settings --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assessment Settings</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                <input type="text" x-model="quiz.title"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                <select x-model="quiz.type"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    <option value="quiz">Quiz (Short, Auto-graded)</option>
                    <option value="exam">Exam (Timed, Weighted)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time Limit
                    (minutes)</label>
                <input type="number" x-model.number="quiz.time_limit" min="0"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                <p class="text-xs text-gray-500 mt-1">0 = No limit</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Attempts</label>
                <input type="number" x-model.number="quiz.max_attempts" min="1"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Passing Score (%)</label>
                <input type="number" x-model.number="quiz.passing_score" min="0" max="100"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
                <input type="number" x-model.number="quiz.weight" min="0" max="100"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                <p class="text-xs text-gray-500 mt-1">e.g., 25 for 25% of final grade.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Questions per
                    Attempt</label>
                <input type="number" x-model.number="quiz.questions_per_attempt" min="1"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                <p class="text-xs text-gray-500 mt-1">Leave blank to use all questions</p>
            </div>

            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" x-model="quiz.randomize" class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Randomize Questions</span>
                </label>
            </div>

            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" x-model="quiz.show_answers_after_submit"
                        class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show Answers After Submit</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Questions List --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Questions</h3>
            <button type="button" @click="addQuestion()"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                + Add Question
            </button>
        </div>

        <div class="space-y-4">
            <template x-for="(question, index) in questions" :key="index">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-sm font-medium text-gray-500">Question <span x-text="index + 1"></span></span>
                        <button type="button" @click="removeQuestion(index)" class="text-red-500 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-3">
                        {{-- Question Content --}}
                        <div>
                            <textarea x-model="question.content" placeholder="Enter question text..." rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            {{-- Question Type --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                                <select x-model="question.type" @change="onTypeChange(index)"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                    <option value="mcq">Multiple Choice (Single Answer)</option>
                                    <option value="multiple">Multiple Choice (Multiple Answers)</option>
                                    <option value="short_answer">Short Answer</option>
                                    <option value="essay">Essay (Manual Grading)</option>
                                </select>
                            </div>

                            {{-- Points --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Points</label>
                                <input type="number" x-model.number="question.points" min="1"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            </div>
                        </div>

                        {{-- MCQ Options --}}
                        <template x-if="question.type === 'mcq' || question.type === 'multiple'">
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-gray-500">Options</label>
                                <template x-for="(option, optIndex) in question.options" :key="optIndex">
                                    <div class="flex items-center space-x-2">
                                        <template x-if="question.type === 'mcq'">
                                            <input type="radio" :name="'correct_' + index"
                                                :checked="question.correct_answer === option"
                                                @change="question.correct_answer = option" class="text-indigo-600">
                                        </template>
                                        <template x-if="question.type === 'multiple'">
                                            <input type="checkbox"
                                                :checked="(question.correct_answer || []).includes(option)"
                                                @change="toggleCorrectAnswer(index, option)"
                                                class="rounded text-indigo-600">
                                        </template>
                                        <input type="text" x-model="question.options[optIndex]"
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                        <button type="button" @click="removeOption(index, optIndex)"
                                            class="text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addOption(index)"
                                    class="text-sm text-indigo-600 hover:underline">+ Add
                                    Option</button>
                            </div>
                        </template>

                        {{-- Short Answer --}}
                        <template x-if="question.type === 'short_answer'">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Correct Answer(s)</label>
                                <input type="text" x-model="question.correct_answer"
                                    placeholder="Enter correct answer (case-insensitive)"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Separate multiple acceptable answers with commas
                                </p>
                            </div>
                        </template>

                        {{-- Explanation --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Explanation (shown after
                                submit)</label>
                            <textarea x-model="question.explanation" placeholder="Optional explanation..." rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="questions.length === 0">
                <div class="text-center py-8 text-gray-500">
                    <p>No questions yet. Click "Add Question" to create your first question.</p>
                </div>
            </template>
        </div>
    </div>

    {{-- Save Button --}}
    <div class="flex justify-end space-x-3">
        <button type="button" @click="$dispatch('close')"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
            Cancel
        </button>
        <button @click="save()" :disabled="saving"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
            <span x-text="saving ? 'Saving...' : 'Save Assessment'"></span>
        </button>
    </div>
</div>

<script>
    function quizBuilder() {
        return {
            quiz: {
                id: null,
                title: '',
                type: 'quiz',
                time_limit: 0,
                max_attempts: 1,
                randomize: false,
                questions_per_attempt: null,
                passing_score: 60,
                weight: 0,
                show_answers_after_submit: false
            },
            questions: [],
            saving: false,
            lessonId: null,

            async initBuilder(lessonId, assessment) {
                this.lessonId = lessonId;
                if (assessment) {
                    this.quiz = { ...this.quiz, ...assessment };
                    // We need an endpoint to fetch questions for a quiz
                    const response = await fetch(`/api/quizzes/${assessment.id}/questions`);
                    if (response.ok) {
                        this.questions = await response.json();
                    }
                } else {
                    this.quiz = {
                        id: null,
                        title: '',
                        type: 'quiz',
                        time_limit: 0,
                        max_attempts: 1,
                        randomize: false,
                        questions_per_attempt: null,
                        passing_score: 60,
                        weight: 0,
                        show_answers_after_submit: false
                    };
                    this.questions = [];
                }
            },

            addQuestion() {
                this.questions.push({
                    content: '',
                    type: 'mcq',
                    options: ['', '', '', ''],
                    correct_answer: null,
                    points: 1,
                    explanation: ''
                });
            },

            removeQuestion(index) {
                this.questions.splice(index, 1);
            },

            addOption(questionIndex) {
                this.questions[questionIndex].options.push('');
            },

            removeOption(questionIndex, optionIndex) {
                this.questions[questionIndex].options.splice(optionIndex, 1);
            },

            onTypeChange(index) {
                const question = this.questions[index];
                if (question.type === 'mcq' || question.type === 'multiple') {
                    question.options = question.options || ['', '', '', ''];
                    question.correct_answer = question.type === 'multiple' ? [] : null;
                } else {
                    question.options = null;
                    question.correct_answer = question.type === 'essay' ? null : '';
                }
            },

            toggleCorrectAnswer(questionIndex, option) {
                const question = this.questions[questionIndex];
                if (!Array.isArray(question.correct_answer)) {
                    question.correct_answer = [];
                }
                const idx = question.correct_answer.indexOf(option);
                if (idx > -1) {
                    question.correct_answer.splice(idx, 1);
                } else {
                    question.correct_answer.push(option);
                }
            },

            async save() {
                if (!this.lessonId) {
                    alert('Lesson ID is required');
                    return;
                }

                this.saving = true;
                try {
                    let url = `/api/lessons/${this.lessonId}/assessments`;
                    let method = 'POST';

                    if (this.quiz.id) {
                        url = `/api/assessments/${this.quiz.id}`;
                        method = 'PUT';
                    }
                    
                    const quizResponse = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.quiz)
                    });

                    const quizData = await quizResponse.json();
                    if (!quizData.success) {
                        throw new Error(quizData.message || 'Failed to save assessment');
                    }

                    // This is a simplification. In a real app, you'd handle question updates/creations/deletions more robustly.
                    // For now, let's just re-add all questions.
                    const quizId = quizData.quiz.id;
                    await fetch(`/api/quizzes/${quizId}/questions/sync`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ questions: this.questions })
                    });


                    this.$dispatch('assessment-saved', quizData.quiz);
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    this.saving = false;
                }
            }
        }
    }
</script>