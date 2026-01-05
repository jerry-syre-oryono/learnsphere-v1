<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

new class extends Component {
    public function approve(User $user, $role)
    {
        $user->is_approved = true;
        $user->save();
        $user->syncRoles([$role]);
        $this->dispatch('user-updated');
    }

    public function reject(User $user)
    {
        $user->delete();
        $this->dispatch('user-updated');
    }

    public function toggleRole(User $user, $role)
    {
        if ($user->hasRole($role)) {
            $user->removeRole($role);
        } else {
            $user->assignRole($role);
        }
        $this->dispatch('user-updated');
    }

    public function with(): array
    {
        return [
            'pendingUsers' => User::where('is_approved', false)->get(),
            'approvedUsers' => User::where('is_approved', true)->with('roles')->get(),
        ];
    }
}; ?>

<div class="space-y-12">
    <!-- Pending Approvals -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Pending Access Requests</h2>
        @if($pendingUsers->isEmpty())
            <p class="text-gray-500 dark:text-gray-400">No pending requests.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Requested</th>
                            <th scope="col" class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingUsers as $user)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $user->name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $user->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 space-x-2">
                                    <button wire:click="approve({{ $user->id }}, 'student')"
                                        class="px-3 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200">Approve as
                                        Student</button>
                                    <button wire:click="approve({{ $user->id }}, 'instructor')"
                                        class="px-3 py-1 bg-green-100 text-green-800 rounded hover:bg-green-200">Approve as
                                        Instructor</button>
                                    <button wire:click="reject({{ $user->id }})"
                                        wire:confirm="Are you sure you want to reject this request?"
                                        class="px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200">Reject</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- User Management -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">User Management</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Roles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvedUsers as $user)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer"
                                            wire:change="toggleRole({{ $user->id }}, 'admin')" {{ $user->hasRole('admin') ? 'checked' : '' }}>
                                        <div
                                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                        </div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Admin</span>
                                    </label>

                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer"
                                            wire:change="toggleRole({{ $user->id }}, 'instructor')" {{ $user->hasRole('instructor') ? 'checked' : '' }}>
                                        <div
                                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                        </div>
                                        <span
                                            class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Instructor</span>
                                    </label>

                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer"
                                            wire:change="toggleRole({{ $user->id }}, 'student')" {{ $user->hasRole('student') ? 'checked' : '' }}>
                                        <div
                                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                        </div>
                                        <span
                                            class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Student</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>