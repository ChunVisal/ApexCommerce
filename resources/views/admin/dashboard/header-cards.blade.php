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
        <x-date-range-picker route="admin.dashboard" />
        <x-export-button :route="route('admin.dashboard.export')" />
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    @foreach ($summaryCards as $card)
        <div
            class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 flex flex-col justify-between relative overflow-hidden h-32  hover:shadow-md dark:hover:shadow-lg dark:hover:shadow-zinc-950/50 transition-shadow duration-200">

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
