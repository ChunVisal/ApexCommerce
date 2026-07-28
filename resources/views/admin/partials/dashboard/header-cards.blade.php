<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Dashboard</h1>
        <div class="flex gap-1 items-center">

            @php
                $hour = now()->format('H');
                if ($hour < 12) {
                    $greeting = 'Good morning';
                } elseif ($hour < 18) {
                    $greeting = 'Good afternoon';
                } else {
                    $greeting = 'Good evening';
                }
            @endphp
            <p class="text-[15px] text-gray-500 dark:text-zinc-400">{{ $greeting }}👋,</p>

            <h4 class="text-[15px] text-gray-600 dark:text-zinc-300"> {{ auth()->user()->name }}</h4>
        </div>
    </div>
    <div class="flex items-center gap-3 mt-3 sm:mt-0">
        {{-- Date Range Button --}}
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

                {{-- Presets --}}
                <div class="space-y-1 mb-3">
                    <a href="{{ route('admin.dashboard', ['start_date' => now()->subDays(6)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                        class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                        Last 7 days
                    </a>
                    <a href="{{ route('admin.dashboard', ['start_date' => now()->subDays(14)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                        class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                        Last 15 days
                    </a>

                    <a href="{{ route('admin.dashboard', ['start_date' => now()->subDays(29)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                        class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                        Last 30 days
                    </a>
                    <a href="{{ route('admin.dashboard', ['start_date' => now()->subDays(89)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                        class="block px-2 py-1.5 text-xs rounded hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                        Last 90 days
                    </a>
                </div>

                <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                    <p class="text-[11px] font-semibold text-gray-500 dark:text-zinc-400 mb-2">Custom range</p>
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="space-y-2">
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full text-xs border border-gray-300 dark:border-zinc-700 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full text-xs border border-gray-300 dark:border-zinc-700 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300">
                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 px-3 py-1.5 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972]">
                                Apply
                            </button>
                            <a href="{{ route('admin.dashboard') }}"
                                class="flex-1 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-600 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 transition text-center">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.dashboard.export') }}"
            class="bg-white dark:bg-zinc-900 text-gray-600 dark:text-zinc-300 flex text-center gap-2 px-3 py-2 text-xs border border-gray-300 dark:border-zinc-800 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4" /> Export
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    @foreach ($summaryCards as $card)
        <div
            class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 flex flex-col justify-between relative overflow-hidden h-32">

            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="rounded-md p-2 px-3"
                        style="background-color: {{ $card['iconBg'] === 'transparent' ? 'transparent' : $card['iconBg'] . '20' }};">
                        <i class="{{ $card['icon'] }} text-[18px]" style="color: {{ $card['iconColor'] }};"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-600 dark:text-zinc-400 uppercase">
                        {{ $card['title'] }}
                    </p>
                </div>
                <button class="text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-200">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
            </div>

            <div class="flex flex-col items-start gap-1">
                <h2
                    class="text-2xl {{ !empty($card['highlight'])
                        ? 'font-mono tabular-nums font-extrabold text-[1.73rem] tracking-tight bg-gradient-to-r from-emerald-700 to-green-600 dark:from-emerald-400 dark:to-green-300 bg-clip-text text-transparent'
                        : 'font-bold text-gray-800 dark:text-zinc-100' }}">
                    {{ $card['value'] }}
                </h2>
                <div class="flex items-center gap-1 text-xs">
                    <span
                        class="font-semibold
                            {{ !empty($card['highlight'])
                                ? 'text-p dark:text-[#1389af]'
                                : ($card['trend'] === 'up'
                                    ? 'text-green-500'
                                    : 'text-red-500') }}
                            flex items-center gap-0.5">
                        <i class="fa-solid fa-arrow-trend-{{ $card['trend'] }}"></i> {{ $card['percentage'] }}
                    </span>
                    <span class="text-gray-600 dark:text-zinc-400">{{ $card['period'] }}</span>

                </div>
            </div>
        </div>
    @endforeach
</div>
<script>
    function dateRangePicker() {
        return {
            open: false,
            displayText: 'Last 14 Days',
            startDate: '',
            endDate: '',

            setRange(range, label) {
                this.open = false;
                const now = new Date();

                switch (range) {
                    case 'today':
                        this.startDate = this.formatDate(now);
                        this.endDate = this.formatDate(now);
                        break;
                    case 'yesterday':
                        const yesterday = new Date(now);
                        yesterday.setDate(yesterday.getDate() - 1);
                        this.startDate = this.formatDate(yesterday);
                        this.endDate = this.formatDate(yesterday);
                        break;
                    case '7days':
                        const weekAgo = new Date(now);
                        weekAgo.setDate(weekAgo.getDate() - 6);
                        this.startDate = this.formatDate(weekAgo);
                        this.endDate = this.formatDate(now);
                        break;
                    case '30days':
                        const monthAgo = new Date(now);
                        monthAgo.setDate(monthAgo.getDate() - 29);
                        this.startDate = this.formatDate(monthAgo);
                        this.endDate = this.formatDate(now);
                        break;
                    case 'thisMonth':
                        this.startDate = this.formatDate(new Date(now.getFullYear(), now.getMonth(), 1));
                        this.endDate = this.formatDate(now);
                        break;
                    case 'lastMonth':
                        this.startDate = this.formatDate(new Date(now.getFullYear(), now.getMonth() - 1, 1));
                        this.endDate = this.formatDate(new Date(now.getFullYear(), now.getMonth(), 0));
                        break;
                }

                this.displayText = label;
                this.updateChart();
            },

            setCustomRange() {
                if (this.startDate && this.endDate) {
                    this.displayText = this.startDate + ' - ' + this.endDate;
                    this.open = false;
                    this.updateChart();
                }
            },

            formatDate(date) {
                return date.toISOString().split('T')[0];
            },

            updateChart() {
                window.location.href = '?start=' + this.startDate + '&end=' + this.endDate;
            },
        };
    }
</script>
