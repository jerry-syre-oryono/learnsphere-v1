<div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
    <div class="p-4">
        <!-- Course Header -->
        <div class="flex justify-between items-start mb-3">
            <div>
                <h3 class="font-semibold text-gray-900">{{ $result->course->title ?? 'Course' }}</h3>
                <p class="text-xs text-gray-500">{{ $result->course->code ?? '' }}</p>
            </div>
            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-sm font-semibold rounded">
                {{ $result->letter_grade }}
            </span>
        </div>

        <!-- Grade Information -->
        <div class="grid grid-cols-2 gap-2 text-sm mb-3">
            <div>
                <p class="text-gray-600">Final Mark</p>
                <p class="font-semibold">{{ number_format($result->final_mark, 1) }}%</p>
            </div>
            <div>
                <p class="text-gray-600">Grade Point</p>
                <p class="font-semibold">{{ number_format($result->grade_point, 1) }}</p>
            </div>
        </div>

        <!-- Retake Indicator -->
        @if($result->is_retake)
            <div class="bg-amber-50 border border-amber-200 rounded p-2 mb-3">
                <p class="text-xs text-amber-800">
                    <strong>Retaken Course:</strong> Grade capped at C per NCHE regulations.
                </p>
                @if($result->was_capped)
                    <p class="text-xs text-amber-700 mt-1">
                        Original: {{ $result->original_grade }} → Capped: {{ $result->capped_grade }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Details Toggle -->
        <button
            wire:click="toggleDetails"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
        >
            {{ $showDetails ? '▼ Hide Details' : '▶ Show Details' }}
        </button>

        <!-- Extended Details -->
        @if($showDetails)
            <div class="mt-3 pt-3 border-t space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Credit Units:</span>
                    <span class="font-semibold">{{ number_format($result->credit_units, 1) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Grade Points Earned:</span>
                    <span class="font-semibold">{{ number_format($result->grade_points_earned, 1) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Semester:</span>
                    <span class="font-semibold">{{ $result->semester }}</span>
                </div>
            </div>
        @endif
    </div>
</div>
