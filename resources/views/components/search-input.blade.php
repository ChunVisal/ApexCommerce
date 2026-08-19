{{-- resources/views/components/search-input.blade.php --}}
<div class="relative flex-1 min-w-[200px]">
    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
    <input type="text" x-model="searchQuery" placeholder="{{ $placeholder ?? 'Search...' }}"
        class="w-full pl-8 pr-8 py-1.5 text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md focus:outline-none focus:ring-1 focus:ring-p placeholder-gray-400 dark:placeholder-zinc-500">
    <button type="button" x-show="searchQuery" @click="searchQuery = ''"
        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
        ✕
    </button>
</div>
