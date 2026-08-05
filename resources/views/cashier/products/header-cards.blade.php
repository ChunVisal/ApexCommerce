<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">My Products</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400">Stock allocated to you</p>
    </div>
    <div class="flex gap-3">
        <button @click="requestOpen = true"
            class="px-3 py-1.5 text-sm font-medium rounded-md bg-p text-gray-100 flex items-center gap-2">
            <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
            Request Restock
        </button>
        <button @click="requestNewProduct = true"
            class="px-3 py-1.5 text-sm font-medium flex items-center gap-2
    text-white bg-emerald-500 dark:bg-emerald-600 border border-emerald-600/20 rounded-md transition-colors shadow-sm">
            <x-heroicon-s-plus-circle class="w-4 h-4 text-white" />
            <span>Request New Product</span>
        </button>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mb-5">
    @foreach ($summaryCards as $card)
        <div
            class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-gray-200 dark:border-zinc-800/80 shadow-sm relative overflow-hidden flex flex-col justify-between group">

            <div class="flex items-center gap-2.5">
                {{-- Minimal Square Inventory Icon Badge --}}
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 border border-gray-100 dark:border-zinc-800/40"
                    style="background-color: {{ $card['iconBg'] ?? '#0F6E8C' }}0D;">
                    <i class="{{ $card['icon'] }} text-sm" style="color: {{ $card['iconColor'] ?? '#0F6E8C' }};"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold tracking-wider text-gray-600 dark:text-zinc-300 uppercase">
                        {{ $card['title'] }}
                    </span>
                    <span class="text-[11px] text-gray-500 dark:text-zinc-400 flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full inline-block animate-pulse"
                            style="background-color: {{ $card['dot'] ?? '#0F6E8C' }};"></span>
                        {{ $card['subtitle'] ?? 'System Tracked' }}
                    </span>
                </div>
            </div>

            {{-- Main Value and Data Analytics Grid --}}
            <div class="mt-4 flex items-baseline justify-between gap-2">
                <h2 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-zinc-100">
                    {{ $card['value'] }}
                </h2>
            </div>

            {{-- Structural Accents like Premium Logistics Trackers --}}
            <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-gray-100 dark:bg-zinc-800/50">
                <div class="h-full transition-all duration-300 group-hover:w-full"
                    style="width: 25%; background-color: {{ $card['iconColor'] ?? '#0F6E8C' }};"></div>
            </div>
        </div>
    @endforeach
</div>


<script></script>
