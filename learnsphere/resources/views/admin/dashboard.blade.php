<x-layouts.app :title="__('Admin Dashboard')">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-slate-900 dark:text-slate-100 border-b border-gray-200 dark:border-slate-700">
                    @livewire('admin.stats')
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
