{{-- resources/views/components/date-range-picker.blade.php --}}
<div class="relative" x-data="{ open: false }">
    <div @click="open = !open"
        class="bg-white dark:bg-zinc-900 flex items-center text-xs gap-2 px-3 py-2 border border-gray-300 dark:border-zinc-800 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer">
        <i class="fa-regular fa-calendar text-gray-800 dark:text-zinc-200"></i>
        <span class="text-xs text-gray-700 dark:text-zinc-300">
            {{ \Carbon\Carbon::parse(request('start_date', now()->subDays(13)))->format('M d, Y') }}
            -
            {{ \Carbon\Carbon::parse(request('end_date', now()))->format('M d, Y') }}
        </span>
        <i class="fa-solid fa-chevron-down text-gray-700 dark:text-zinc-400"></i>
    </div>

    <div x-show="open" @click.outside="open = false" x-cloak
        class="absolute right-0 mt-2 w-72 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-lg z-30 p-3">

        <div class="space-y-1 mb-3">
            <a href="{{ route($route, ['start_date' => now()->subDays(6)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                Last 7 days
            </a>
            <a href="{{ route($route, ['start_date' => now()->subDays(14)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                Last 15 days
            </a>
            <a href="{{ route($route, ['start_date' => now()->subDays(29)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                Last 30 days
            </a>
            <a href="{{ route($route, ['start_date' => now()->subDays(89)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                Last 90 days
            </a>
        </div>

        <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
            <p class="text-[11px] font-semibold text-gray-500 dark:text-zinc-400 mb-2">Custom range</p>
            <form action="{{ route($route) }}" method="GET" class="space-y-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="[&::-webkit-calendar-picker-indicator]:dark:invert w-full text-xs border border-gray-300 dark:border-zinc-700 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="[&::-webkit-calendar-picker-indicator]:dark:invert w-full text-xs border border-gray-300 dark:border-zinc-700 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-3 py-1.5 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972]">
                        Apply
                    </button>
                    <a href="{{ route($route) }}"
                        class="flex-1 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-600 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 transition text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
