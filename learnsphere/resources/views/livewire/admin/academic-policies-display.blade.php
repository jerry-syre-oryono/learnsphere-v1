<div class="space-y-4">
    <h2 class="text-lg font-semibold text-gray-900">Academic Policies & Regulations</h2>

    <div class="grid gap-4">
        @foreach($policies as $policy)
            <div class="bg-white rounded-lg shadow p-4 border-l-4 @switch($policy['policy_type'])
                @case('regulation')
                    border-red-500
                @break
                @case('guideline')
                    border-blue-500
                @break
                @default
                    border-gray-500
            @endswitch">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-semibold text-gray-900">{{ $policy['policy_name'] }}</h3>
                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full @switch($policy['policy_type'])
                        @case('regulation')
                            bg-red-100 text-red-800
                        @break
                        @case('guideline')
                            bg-blue-100 text-blue-800
                        @break
                        @default
                            bg-gray-100 text-gray-800
                    @endswitch">
                        {{ ucfirst($policy['policy_type']) }}
                    </span>
                </div>

                <p class="text-sm text-gray-700 mb-2">{{ $policy['description'] }}</p>

                @if($policy['value'])
                    <div class="bg-gray-50 rounded p-2 text-xs text-gray-600">
                        <strong>Value:</strong> {{ $policy['value'] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if(empty($policies))
        <p class="text-gray-500 text-center py-8">No policies configured yet.</p>
    @endif
</div>
