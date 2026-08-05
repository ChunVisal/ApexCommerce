<div>
    <!-- Title + Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Activity & Audit Logs</h1>
            <p class="text-xs text-gray-500 dark:text-zinc-400">Track all user actions and system changes across your
                store
            </p>
        </div>
        <div class="flex items-center gap-2 mt-3 sm:mt-0">
            <div class="relative" x-data="{ open: false }">
                <div @click="open = !open"
                    class="bg-white dark:bg-zinc-900 flex items-center text-xs gap-2 px-3 py-2 border border-gray-300 dark:border-zinc-800 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition cursor-pointer">
                    <i class="fa-regular fa-calendar text-gray-800 dark:text-zinc-200"></i>
                    <span class="text-xs text-gray-700 dark:text-zinc-300">
                        {{ \Carbon\Carbon::parse(request('start_date', now()->subDays(14)))->format('M d, Y') }}
                        -
                        {{ \Carbon\Carbon::parse(request('end_date', now()))->format('M d, Y') }}
                    </span>
                    <i class="fa-solid fa-chevron-down text-gray-700 dark:text-zinc-400"></i>
                </div>

                <div x-show="open" @click.outside="open = false" x-cloak
                    class="absolute right-0 mt-2 w-72 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-lg dark:shadow-zinc-950/50 z-30 p-3">

                    {{-- Presets --}}
                    <div class="space-y-1 mb-3">
                        <a href="{{ route('admin.activitylog', ['start_date' => now()->subDays(14)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">
                            Last 15 days
                        </a>
                        <a href="{{ route('admin.activitylog', ['start_date' => now()->subDays(6)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">
                            Last 7 days
                        </a>
                        <a href="{{ route('admin.activitylog', ['start_date' => now()->subDays(29)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">
                            Last 30 days
                        </a>
                        <a href="{{ route('admin.activitylog', ['start_date' => now()->subDays(89)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">
                            Last 90 days
                        </a>
                    </div>

                    <div class="border-t border-gray-200 dark:border-zinc-800 pt-3">
                        <p class="text-[11px] font-semibold text-gray-500 dark:text-zinc-400 mb-2">Custom range</p>
                        <form action="{{ route('admin.activitylog') }}" method="GET" class="space-y-2">
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="[&::-webkit-calendar-picker-indicator]:dark:invert w-full text-xs border border-gray-300 dark:border-zinc-800 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 focus:outline-none focus:border-[#0F6E8C]">
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="[&::-webkit-calendar-picker-indicator]:dark:invert w-full text-xs border border-gray-300 dark:border-zinc-800 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 focus:outline-none focus:border-[#0F6E8C]">
                            <button type="submit"
                                class="w-full px-3 py-1.5 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition-colors">
                                Apply
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.activitylog.export', request()->all()) }}"
                class="bg-white dark:bg-zinc-900 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                Export Logs
            </a>
            <button onclick="clearLogs()"
                class="bg-red-50 dark:bg-red-900/20 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-md hover:bg-red-100 dark:hover:bg-red-900/30 transition">
                <i class="fa-solid fa-trash-can"></i> Clear Logs
            </button>
        </div>
    </div>
    {{-- Summary Cards Component --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        @foreach ($summaryCards as $card)
            <div
                class="relative bg-white dark:bg-zinc-900 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 p-3 flex flex-col justify-between hover:shadow-md transition-all h-32 overflow-hidden">

                {{-- Top Row: Icon & Title --}}
                <div class="flex items-center justify-start gap-2">
                    <div class="rounded-md p-2 px-3 shrink-0" style="background-color: {{ $card['iconBg'] }}20;">
                        <i class="{{ $card['icon'] }} text-[16px]" style="color: {{ $card['iconColor'] }};"></i>
                    </div>
                    <h2 class="text-xs font-bold tracking-wider text-gray-500 dark:text-zinc-400 uppercase truncate">
                        {{ $card['title'] }}
                    </h2>
                </div>

                {{-- Value --}}
                <h2 class="py-0.5 text-2xl font-bold text-gray-800 dark:text-zinc-100 truncate">
                    {{ $card['value'] }}
                </h2>

                {{-- Bottom Row: Trend & Subtitle --}}
                <div class="flex items-center justify-start gap-1.5 overflow-hidden">
                    @if (isset($card['trend']))
                        <span
                            class="text-xs font-semibold {{ $card['trendColor'] ?? 'text-green-500' }} flex items-center shrink-0">
                            <i
                                class="fa-solid {{ ($card['trendDirection'] ?? 'up') === 'up' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-[10px] mr-1"></i>
                            {{ $card['trend'] }}
                        </span>
                    @endif
                    <p class="text-[12px] text-gray-500 dark:text-zinc-400 truncate">{{ $card['subtitle'] }}</p>
                </div>

            </div>
        @endforeach
    </div>
</div>
