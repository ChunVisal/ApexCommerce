<div class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Daily Sales</h3>
        <span class="text-xs font-medium text-gray-500 dark:text-zinc-300">{{ $dailySales->sum('orders') }} orders ·
            ${{ number_format($dailySales->sum('revenue'), 2) }} total</span>
    </div>

    {{-- Chart --}}
    <div class="p-4 border-b border-gray-100 dark:border-zinc-800">
        <div style="height: 260px;">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </div>

    {{-- Table --}}
    <x-table headers="headers.dailySales" rows="paginatedDailySales">
        <td class="py-3  font-medium text-gray-800 dark:text-zinc-200"
            x-text="new Date(row.date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: '2-digit', year: 'numeric' })">
        </td>
        <td class="py-3  text-gray-700 dark:text-zinc-200" x-text="row.orders"></td>
        <td class=" text-gray-700 dark:text-zinc-200" x-text="row.items_qty"></td>
        <td class=" font-bold text-gray-800 dark:text-zinc-200" x-text="'$' + Number(row.revenue).toFixed(2)"></td>
        <td class="  font-semibold text-red-500" x-text="'-$' + Number(row.discount).toFixed(2)">
        </td>
        <td class=" font-semibold text-yellow-600" x-text="'-$' + Number(row.vip_discount).toFixed(2)">
        </td>
        <td class="  text-gray-600 dark:text-zinc-300" x-text="'$' + Number(row.tax).toFixed(2)">
        </td>
        <td class=" font-bold text-[#0F6E8C]"
            x-text="'$' + (row.revenue - row.discount - row.vip_discount - row.tax).toFixed(2)"></td>
        <x-slot:empty>
            <x-empty-state icon="heroicon-o-chart-bar" x-show="dailySales.length === 0" title="No sales data"
                description="There are no sales in the selected date range." />
        </x-slot:empty>
    </x-table>
</div>

{{-- Chart Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('dailySalesChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Smooth wave blue gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(15, 110, 140, 0.35)');
        gradient.addColorStop(1, 'rgba(15, 110, 140, 0.0)');

        const gridColor = 'rgba(0, 0, 0, 0.1)';
        const textColor = '#71717A';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($dailySales->pluck('date')->map(fn($d) => Carbon\Carbon::parse($d)->format('M d'))),
                datasets: [{
                    label: 'Revenue',
                    data: @json($dailySales->pluck('revenue')),
                    borderColor: '#0F6E8C',
                    borderWidth: 2.5,
                    backgroundColor: gradient,
                    fill: true,
                    pointBackgroundColor: '#0F6E8C',
                    pointBorderColor: '#18181B',
                    pointBorderWidth: 2,
                    pointRadius: 0, // Hides dots by default
                    pointHoverRadius: 6, // Shows dot on hover
                    pointHitRadius: 10 // Larger touch/hover zone
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false // Shows data when hovering anywhere on the vertical axis
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: '#000000',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        bodyFont: {
                            weight: 'bold'
                        },
                        borderColor: '#E4E4E7',
                        borderWidth: 1,

                        padding: 10,
                        boxPadding: 4,
                        callbacks: {
                            label: (ctx) => ' Revenue: $' + Number(ctx.raw).toLocaleString(undefined, {
                                minimumFractionDigits: 2
                            })
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor,
                            drawBorder: false
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 11,
                            },
                            callback: v => '$' + v.toLocaleString()
                        }
                    }
                }
            }
        });
    });
</script>
