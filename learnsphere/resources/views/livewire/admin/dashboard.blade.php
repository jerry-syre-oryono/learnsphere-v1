<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <div class="mb-12">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Management Dashboard</h2>
            <a href="{{ route('admin.courses.create') }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                + Create Course
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Active Users</h3>
                <p class="text-3xl font-bold mt-2 text-indigo-600">120</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Total Courses</h3>
                <p class="text-3xl font-bold mt-2 text-green-600">15</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Revenue</h3>
                <p class="text-3xl font-bold mt-2 text-blue-600">$12,450</p>
            </div>
        </div>
    </div>
</div>