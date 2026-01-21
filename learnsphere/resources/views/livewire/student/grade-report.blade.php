<div class="space-y-6">
    <!-- CGPA and Classification Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-md p-6 text-white">
        <div class="grid grid-cols-3 gap-4">
            <!-- CGPA -->
            <div class="text-center">
                <p class="text-sm opacity-90">Cumulative GPA</p>
                <p class="text-4xl font-bold">{{ number_format($gradeReport['cgpa'], 2) }}</p>
                <p class="text-xs mt-1 opacity-75">Out of 5.0</p>
            </div>

            <!-- Classification -->
            <div class="text-center border-l border-r border-white/20">
                <p class="text-sm opacity-90">Classification</p>
                <p class="text-2xl font-bold">{{ $gradeReport['classification']['classification'] ?? $gradeReport['classification']['class'] ?? 'N/A' }}</p>
                <p class="text-xs mt-1 opacity-75">
                    @if($gradeReport['is_eligible_for_graduation'])
                        ✓ Eligible for Graduation
                    @else
                        ✗ Not Eligible Yet
                    @endif
                </p>
            </div>

            <!-- Academic Standing -->
            <div class="text-center">
                <p class="text-sm opacity-90">Academic Standing</p>
                <p class="text-xl font-bold">{{ $gradeReport['standing']['status'] }}</p>
                <p class="text-xs mt-1 opacity-75">
                    @if($gradeReport['standing']['on_probation'])
                        <span class="inline-block px-2 py-1 bg-red-500 rounded text-xs">On Probation</span>
                    @else
                        <span class="inline-block px-2 py-1 bg-green-500 rounded text-xs">Good Standing</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Academic Policy Notice -->
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
        <p class="text-sm text-amber-800">
            <strong>NCHE Policy:</strong> A candidate shall not graduate with a CGPA below 2.00.
        </p>
    </div>

    <!-- Additional Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-blue-500">
            <h3 class="font-semibold text-gray-900 mb-2">Graduation Eligibility</h3>
            <p class="text-sm text-gray-600">
                @if($gradeReport['is_eligible_for_graduation'])
                    ✓ Your CGPA meets the minimum requirement for graduation.
                @else
                    Current CGPA: {{ number_format($gradeReport['cgpa'], 2) }} (Minimum required: 2.00)
                @endif
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border-t-4 border-green-500">
            <h3 class="font-semibold text-gray-900 mb-2">Study Continuation</h3>
            <p class="text-sm text-gray-600">
                @if($gradeReport['can_continue_studies'])
                    ✓ You are eligible to continue with your studies.
                @else
                    ✗ You need to improve your academic performance.
                @endif
            </p>
        </div>
    </div>
</div>
