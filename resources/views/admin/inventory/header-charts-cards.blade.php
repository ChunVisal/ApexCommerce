<!-- Title + Actions -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Inventory</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400">Track stock levels and movement across your catalog</p>
    </div>
    <div class="flex items-center gap-2 mt-3 sm:mt-0">

        <x-date-range-picker route="admin.inventory" />
        
        <x-export-button :route="route('admin.inventory.export')" />
    </div>
</div>

<!-- Stock Movement Chart + Summary Cards-->
<div class="flex gap-2 w-full min-w-0 mb-4">
    <!-- Stock Movement Overview (chart) -->
    <div
        class="w-[63%] min-w-0 bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60 p-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Stock Movement Overview</h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">
                    Stock In vs Stock Out — {{ count($trend['labels']) }} days
                </p>
            </div>
            <div class="flex items-center gap-3 text-[11px] text-gray-500 dark:text-zinc-400">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></span>Stock
                    In</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span>Stock
                    Out</span>
                <a href="{{ route('admin.inventory.movements', ['start_date' => request('start_date', now()->subDays(14)->format('Y-m-d')), 'end_date' => request('end_date', now()->format('Y-m-d'))]) }}"
                    class="text-[11px] text-[#0F6E8C] hover:underline">
                    View All Movements →
                </a>
            </div>
        </div>
        <div class="relative min-w-0" style="height: 260px;">
            <canvas id="movementTrendChart"></canvas>
        </div>
    </div>

    <!-- Summary Cards (2 col x 2 row)  inventory -->
    <div class="w-[37%] min-w-0 grid grid-cols-2 grid-rows-2 gap-2">
        @foreach ($summaryCards as $card)
            <div
                class="bg-white dark:bg-zinc-900 p-3 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 flex flex-col justify-between relative overflow-hidden">
                <div class="flex flex-col items-start gap-1.5 xl:flex-row xl:items-center 2xl:gap-2">
                    <div class="rounded-md p-2 px-3 shrink-0"
                        style="background-color: {{ $card['iconBg'] === 'transparent' ? 'transparent' : $card['iconBg'] . '20' }};">
                        <i class="{{ $card['icon'] }} text-[16px]" style="color: {{ $card['iconColor'] }};"></i>
                    </div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-gray-600 dark:text-zinc-400 uppercase leading-tight">
                        {{ $card['title'] }}</p>
                </div>
                <div class="flex flex-col items-start gap-1">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-zinc-100">
                        @if ($card['title'] === 'Stock Value')
                            {{ $card['value'] }}.00
                        @else
                            {{ $card['value'] }}
                        @endif
                    </h2>

                    <div class="flex items-start gap-1 text-[12px]">
                        <span
                            class="font-semibold {{ $card['trend'] === 'up' ? 'text-green-500' : 'text-red-500' }} flex items-center gap-0.5">
                            <i class="fa-solid fa-arrow-trend-{{ $card['trend'] }}"></i>
                            {{ $card['percentage'] }}
                        </span>
                        <span class="text-gray-500 dark:text-zinc-400 whitespace-nowrap">{{ $card['period'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ---------- Chart ----------
        const isDarkMode = document.documentElement.classList.contains('dark');
        const trendCanvas = document.getElementById('movementTrendChart');
        if (!trendCanvas) return;

        const existingTrend = Chart.getChart(trendCanvas);
        if (existingTrend) existingTrend.destroy();

        const trendDetails = @json($trend['details']);
        const trendLabels = @json($trend['labels']);
        const trendStockIn = @json($trend['stock_in']);
        const trendStockOut = @json($trend['stock_out']);

        new Chart(trendCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Stock In',
                    data: trendStockIn,
                    backgroundColor: '#10B981',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.6,
                    minBarLength: 3,
                }, {
                    label: 'Stock Out',
                    data: trendStockOut,
                    backgroundColor: '#EF4444',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.6,
                    minBarLength: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 100,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        yAlign: 'bottom',
                        callbacks: {
                            footer: function(context) {
                                const idx = context[0].dataIndex;
                                const detail = trendDetails[idx];
                                if (detail && detail.length > 0) {
                                    return detail.split(', '); // each on its own line
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#787878',
                            font: {
                                size: 11
                            }
                        },
                        border: {
                            display: false
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#787878',
                            font: {
                                size: 11
                            },
                            stepSize: 5
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.15)',
                            borderDash: [4, 4]
                        },
                        border: {
                            display: false
                        },
                    }
                },
            },
        });

        // ---------- Scroll to search ----------
        @if (request('search'))
            const searchEl = document.getElementById('searchSection');
            if (searchEl) {
                setTimeout(() => {
                    searchEl.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    document.getElementById('search').focus();
                }, 100);
            }
        @endif

    }); // end DOMContentLoaded
</script>
