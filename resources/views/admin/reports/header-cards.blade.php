<!-- Title + Date Range + Export -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Reports</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400">Sales, Analyze, and staff performance insights</p>
    </div>
    <div class="flex items-center gap-2 mt-3 sm:mt-0">
        <a href="{{ route('admin.reports') }}"
            class="bg-yellow-500 dark:bg-yellow-700 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-100 dark:text-zinc-300 border border-gray-300 dark:border-zinc-800 rounded-md hover:bg-yellow-500/90 dark:hover:bg-yellow-800 transition">
            <x-heroicon-m-arrow-path class="w-4 h-4" />
            Refresh
        </a>

        <x-date-range-picker route="admin.reports" />
        <x-export-button :route="route('admin.dashboard.export')" />
    </div>
</div>

<!-- Summary Cards (LOOP) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    @foreach ($summaryCards as $card)
        <div
            class="relative bg-white dark:bg-zinc-900 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 p-3 flex flex-col justify-between hover:shadow-md transition-all h-32 overflow-hidden">

            {{-- Top Right Rotated Corner Ribbon Badge --}}
            @if (!empty($card['badge']))
                <div class="absolute -top-1 -right-1 w-16 h-16 pointer-events-none overflow-hidden z-10">
                    <span
                        class="absolute top-3 -right-6 w-24 text-center text-[9px] font-bold tracking-wider text-white shadow-sm bg-[#DDCE00] dark:bg-yellow-500/60"
                        style="padding-top: 2px; padding-bottom: 2px; border-radius: 2px; box-shadow: 0 2px 6px 0 rgba(0,0,0,0.08); transform: rotate(45deg); text-transform: uppercase;">
                        {{ $card['badge'] }}
                    </span>
                </div>
            @endif

            {{-- Top Row: Icon & Title --}}
            <div class="flex items-center justify-start gap-2">
                <div class="rounded-md p-2 px-3 shrink-0" style="background-color: {{ $card['iconBg'] }}20;">
                    <i class="{{ $card['icon'] }} text-[16px]" style="color: {{ $card['iconColor'] }};"></i>
                </div>
                <h2 class="text-xs font-bold tracking-wider text-gray-600 dark:text-zinc-400 uppercase truncate">
                    {{ $card['title'] }}
                </h2>
            </div>

            {{-- Middle Row: Value --}}
            <h2 class="py-0.5 text-2xl font-bold text-gray-800 dark:text-zinc-100 truncate">
                {{ $card['value'] }}
            </h2>

            {{-- Bottom Row: Trend & Subtitle / Period --}}
            <div class="flex items-center justify-start gap-1.5 overflow-hidden">
                @if (isset($card['percentage']))
                    <span
                        class="text-xs font-semibold flex items-center shrink-0 {{ ($card['trend'] ?? 'up') === 'up' ? 'text-green-500' : 'text-red-500' }}">
                        @if (($card['trend'] ?? 'up') === 'up')
                            <i class="fa-solid fa-arrow-trend-up text-[10px] mr-1"></i>
                        @else
                            <i class="fa-solid fa-arrow-trend-down text-[10px] mr-1"></i>
                        @endif
                        {{ $card['percentage'] }}
                    </span>
                @endif

                <p class="text-xs text-gray-500 dark:text-zinc-400 truncate">
                    {{ $card['period'] ?? ($card['subtitle'] ?? '') }}
                </p>
            </div>

        </div>
    @endforeach
</div>
