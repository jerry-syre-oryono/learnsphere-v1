<div class="space-y-3">
    <!-- GPA / CGPA Display -->
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
            <p class="text-xs text-gray-600">Semester GPA</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($gpa, 2) }}</p>
        </div>
        <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
            <p class="text-xs text-gray-600">Cumulative GPA</p>
            <p class="text-2xl font-bold text-purple-700">{{ number_format($cgpa, 2) }}</p>
        </div>
    </div>

    <!-- Classification Badge -->
    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-3 border border-indigo-200">
        <p class="text-xs text-gray-600 mb-1">Classification</p>
        <div class="flex items-center gap-2">
            <span class="inline-block px-3 py-1 bg-indigo-600 text-white text-sm font-semibold rounded-full">
                {{ $classification['classification'] ?? $classification['class'] ?? 'Unclassified' }}
            </span>
        </div>
    </div>

    <!-- Academic Standing -->
    <div class="bg-white rounded-lg p-3 border-l-4 @if($standing['on_probation']) border-red-500 @else border-green-500 @endif">
        <p class="text-xs text-gray-600 mb-1">Academic Standing</p>
        <p class="font-semibold text-sm @if($standing['on_probation']) text-red-700 @else text-green-700 @endif">
            {{ $standing['status'] }}
        </p>
        @if($standing['on_probation'])
            <p class="text-xs text-red-600 mt-1">⚠️ {{ $standing['message'] }}</p>
        @endif
    </div>
</div>
