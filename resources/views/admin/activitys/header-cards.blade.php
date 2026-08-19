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

            <x-date-range-picker route="admin.activitylog" />
            
            <x-export-button :route="route('admin.activitylog.export', request()->all())" />
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
