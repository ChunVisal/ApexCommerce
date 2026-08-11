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
    <div class="overflow-x-auto tab-container max-h-[400px] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white dark:bg-zinc-900">
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="py-3 pl-4 font-medium">Date</th>
                    <th class="py-3 px-4 font-medium text-center">Orders</th>
                    <th class="py-3 px-4 font-medium text-right">Revenue</th>
                    <th class="py-3 px-4 font-medium text-right">Discount</th>
                    <th class="py-3 px-4 font-medium text-right">VIP Discount</th>
                    <th class="py-3 px-4 font-medium text-right">Tax</th>
                    <th class="py-3 pr-7 font-medium text-right">Net Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                @forelse($dailySales as $sale)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pl-4 font-medium text-gray-800 dark:text-zinc-200">
                            {{ Carbon\Carbon::parse($sale->date)->format('D, M d, Y') }}
                        </td>
                        <td class="py-3 px-4 text-center text-gray-700 dark:text-zinc-200">
                            {{ $sale->orders }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-800 dark:text-zinc-200">
                            ${{ number_format($sale->revenue, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-red-500">
                            -${{ number_format($sale->discount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-yellow-600">
                            -${{ number_format($sale->vip_discount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-gray-600 dark:text-zinc-300">
                            ${{ number_format($sale->tax, 2) }}
                        </td>
                        <td class="py-3 pr-7 text-right font-bold text-[#0F6E8C]">
                            ${{ number_format($sale->revenue - $sale->discount - $sale->vip_discount - $sale->tax, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400 text-sm">No sales data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
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
                    tension: 0.4,
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
