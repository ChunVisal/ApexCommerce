{{-- resources/views/components/filter-select.blade.php --}}
<div class="relative">
    <select x-model="{{ $model }}"
        class="bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-p cursor-pointer">
        {{ $slot }}
    </select>
    <x-heroicon-o-chevron-down
        class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none"
        stroke-width="2" />
</div>
