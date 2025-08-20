<x-layouts.app :title="__('Dashboard')">
    <div class="relative flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @php
            $user = Auth::user();
            $role = $user->getRoleNames()->first();
        @endphp
        @if ($role)
            <div class="absolute top-4 right-4 p-4 rounded-lg shadow-md text-lg font-semibold w-fit z-10 role-{{ strtolower($role) }}">
                You are logged in as a {{ ucfirst($role) }}.
            </div>
        @endif
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
