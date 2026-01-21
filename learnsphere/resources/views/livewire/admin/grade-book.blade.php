<div>
    <div class="mb-4">
        <input wire:model.debounce.300ms="search" type="text" placeholder="Search students by name or email..." class="w-full px-3 py-2 border rounded">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th wire:click="sort('enrollment.user.name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Student
                    </th>
                    <th wire:click="sort('letter_grade')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Letter Grade
                    </th>
                    <th wire:click="sort('grade_point')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Grade Point
                    </th>
                    <th wire:click="sort('grade_points_earned')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Points Earned
                    </th>
                    <th wire:click="sort('was_capped')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Capped
                    </th>
                    <th wire:click="sort('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Processed On
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($this->courseResults as $result)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $result->enrollment->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $result->enrollment->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $result->letter_grade }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $result->grade_point }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $result->grade_points_earned }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="{{ $result->was_capped ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }} px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ $result->was_capped ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $result->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            No grade results found for this course.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->courseResults->links() }}
    </div>
</div>