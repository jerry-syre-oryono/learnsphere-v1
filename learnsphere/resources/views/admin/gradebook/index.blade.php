<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">
                {{ __('Gradebook') }}
            </h2>

            <div class="space-y-8">
                @foreach ($courses as $course)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $course->title }}</h3>

                        @if ($course->assignments->count() === 0)
                            <div class="text-sm text-gray-500">No assignments for this course yet.</div>
                        @else
                            <div class="space-y-6">
                                @foreach($course->assignments as $assignment)
                                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $assignment->title }}</h4>
                                                @if($assignment->due_date)
                                                    <div class="text-xs text-gray-500">Due: {{ $assignment->due_date->format('M d, Y') }}</div>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">Max: {{ $assignment->max_score ?? '-' }}</div>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead class="bg-white dark:bg-gray-700">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student Number</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Attachment</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Percentage</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    @forelse($course->students as $student)
                                                        @php
                                                            $submission = $student->submissions->where('submittable_type', get_class($assignment))->where('submittable_id', $assignment->id)->first();
                                                            $enrollment = $course->enrollments->where('user_id', $student->id)->first();
                                                            $submissionKey = 'submission-' . ($submission?->id ?? 'none');
                                                        @endphp
                                                        <tr data-submission-key="{{ $submissionKey }}">
                                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $enrollment?->student_number ?? 'N/A' }}</td>
                                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $student->name }}</td>
                                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $submission ? 'Yes' : 'No' }}</td>
                                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                                @if($submission && $submission->attachment_path)
                                                                    <a href="{{ route('submission.download', $submission) }}" class="text-indigo-600 hover:text-indigo-800 text-xs">Download</a>
                                                                    <div class="text-xs text-gray-400">{{ $submission->attachment_name }}</div>
                                                                @else
                                                                    <span class="text-xs text-gray-400">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="score-cell px-4 py-3 text-sm text-gray-500">{{ $submission && $submission->score !== null ? $submission->score : '-' }}</td>
                                                            <td class="percentage-cell px-4 py-3 text-sm text-gray-500">{{ $submission && $submission->percentage !== null ? number_format($submission->percentage, 2) . '%' : '-' }}</td>
                                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                                @if($submission)
                                                                    <form method="POST" action="{{ route('submission.grade', $submission) }}" class="submission-grade-form flex gap-2 items-center" data-submission-id="{{ $submission->id }}" style="flex-wrap:wrap">
                                                                        @csrf
                                                                        <input name="score" type="number" step="0.01" min="0" placeholder="Score" value="{{ old('score', $submission->score) }}" class="w-20 px-2 py-1 border rounded text-sm" />
                                                                        <input name="feedback" type="text" placeholder="Feedback" value="{{ old('feedback', $submission->feedback) }}" class="px-2 py-1 border rounded text-sm" />
                                                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded">Save</button>
                                                                        <div class="submission-msg text-xs text-green-600 ml-2"></div>
                                                                    </form>
                                                                @else
                                                                    <span class="text-xs text-gray-400">Awaiting submission</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="px-4 py-3 text-sm text-gray-500 text-center">No students enrolled.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.submission-grade-form').forEach(function(form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            var formData = new FormData(form);
            btn.disabled = true;
            try {
                var res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                if (!res.ok) throw res;
                var data = await res.json();
                var sub = data.submission;

                // Find the correct row using the form's data attribute
                var submissionId = form.getAttribute('data-submission-id');
                var parentRow = form.closest('tr');
                if (!parentRow) {
                    console.error('Could not find parent row for form.', form);
                    return;
                }

                // Update cells using class selectors (more reliable than ID selectors)
                var scoreEl = parentRow.querySelector('.score-cell');
                var percEl = parentRow.querySelector('.percentage-cell');

                if (scoreEl) scoreEl.textContent = (sub.score !== null ? sub.score : '-');
                if (percEl) percEl.textContent = (sub.percentage !== null ? parseFloat(sub.percentage).toFixed(2) + '%' : '-');

                var msg = form.querySelector('.submission-msg');
                if (msg) {
                    msg.textContent = data.message || 'Saved';
                    setTimeout(function(){ msg.textContent = ''; }, 3000);
                }
            } catch (err) {
                var msg = form.querySelector('.submission-msg');
                if (msg) {
                    msg.textContent = 'Error saving';
                    setTimeout(function(){ msg.textContent = ''; }, 5000);
                }
            } finally {
                btn.disabled = false;
            }
        });
    });
});
</script>
@endpush
