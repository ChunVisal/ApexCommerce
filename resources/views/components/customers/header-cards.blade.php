{{-- resources/views/components/customers/header-cards.blade.php --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Customers</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400">Manage your customer relationships and loyalty</p>
    </div>
    <x-export-button :route="$exportRoute" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    @foreach ($summaryCards as $card)
        <div
            class="bg-white dark:bg-zinc-900 p-3 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 flex flex-col justify-between relative overflow-hidden h-32">

            @if (isset($card['badge']))
                <div class="absolute -top-1 -right-1 w-16 h-16 pointer-events-none overflow-hidden z-10">
                    <span
                        class="absolute top-3 -right-6 w-24 text-center text-[9px] font-bold tracking-wider text-white shadow-sm bg-[#DDCE00] dark:bg-yellow-500/60"
                        style="padding-top: 2px; padding-bottom: 2px; border-radius: 2px; box-shadow: 0 2px 6px 0 rgba(0,0,0,0.08); transform: rotate(45deg); text-transform: uppercase;">
                        {{ $card['badge'] }}
                    </span>
                </div>
            @endif

            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="rounded-md p-2 px-3"
                        style="background-color: {{ $card['iconBg'] === 'transparent' ? 'transparent' : $card['iconBg'] . '20' }};">
                        <i class="{{ $card['icon'] }} text-[18px]" style="color: {{ $card['iconColor'] }};"></i>
                    </div>
                    <p class="text-xs font-bold tracking-wider text-gray-600 dark:text-zinc-400 uppercase">
                        {{ $card['title'] }}</p>
                </div>
            </div>

            <div class="flex flex-col items-start gap-1">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-zinc-100">{{ $card['value'] }}</h2>
            </div>

            <div class="flex items-center text-center justify-start gap-1">
                <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $card['subtitle'] }}</p>
            </div>
        </div>
    @endforeach
</div>
