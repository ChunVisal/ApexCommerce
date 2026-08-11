{{-- ============================================================
     Dashboard › Charts
     Sales Overview (line) + Payment breakdown (donut)
     ============================================================ --}}
<div class="flex gap-4 w-full min-w-0">

    {{-- ── Sales Overview (line chart) ── --}}
    <div
        class="w-2/3 min-w-0 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm p-5 overflow-hidden border border-gray-200 dark:border-zinc-800/50">

        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-[15px] font-semibold text-gray-800 dark:text-zinc-100">Sales Overview</h3>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                    Total: <span
                        class="font-semibold text-[#0F6E8C] dark:text-[#1389af] ">${{ $salesChart['total_revenue'] }}</span>
                    · {{ count($salesChart['chart']) }} days
                </p>
            </div>
            <button type="button" class="text-gray-500 dark:text-zinc-400 hover:text-gray-600 dark:hover:text-zinc-300">
                <x-heroicon-s-ellipsis-vertical class="w-6 h-6" />
            </button>

        </div>

        <div class="relative min-w-0" style="height: 200px;">
            <canvas id="salesOverviewChart"></canvas>
        </div>
    </div>

    {{-- ── Payment Breakdown (donut chart) ── --}}
    <div
        class="w-1/3 min-w-0 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm p-5 overflow-hidden border border-gray-200 dark:border-zinc-800/50">

        <div class="flex items-center justify-between mb-2">
            <h3 class="text-[15px] font-semibold text-gray-800 dark:text-zinc-100">Payment</h3>
            <button type="button"
                class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <circle cx="12" cy="5" r="1.6" />
                    <circle cx="12" cy="12" r="1.6" />
                    <circle cx="12" cy="19" r="1.6" />
                </svg>
            </button>
        </div>

        <div class="relative min-w-0" style="height: 180px;">
            <canvas id="paymentChart"></canvas>
        </div>

        <div class="flex items-center justify-center gap-4 mt-2">
            <span class="flex items-center gap-2 text-[12px] text-gray-600 dark:text-zinc-400">
                <span class="w-3 h-3 rounded-full" style="background:#a262e0"></span> Cash
            </span>
            <span class="flex items-center gap-2 text-[12px] text-gray-600 dark:text-zinc-400">
                <span class="w-3 h-3 rounded-full" style="background:#c9a3ec"></span> Credit/Debit
            </span>
            <span class="flex items-center gap-2 text-[12px] text-gray-600 dark:text-zinc-400">
                <span class="w-3 h-3 rounded-full" style="background:#e9d9f8"></span> KHQR
            </span>
        </div>
    </div>

</div>

<style>
    #salesTooltip {
        transition: opacity 0.15s ease;
    }
</style>

{{-- ── Chart JS ── --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const labelColor = '#787878';

        // ── Sales Overview Line Chart ──────────────────────────────────
        const salesCtx = document.getElementById('salesOverviewChart').getContext('2d');
        const gradient = salesCtx.createLinearGradient(0, 0, 0, 180);
        gradient.addColorStop(0, 'rgba(249, 115, 22, 0.25)');
        gradient.addColorStop(1, 'rgba(249, 115, 22, 0)');

        const labels = @json(array_column($salesChart['chart'], 'label_short'));
        const fullLabels = @json(array_column($salesChart['chart'], 'label_full'));
        const data = @json(array_column($salesChart['chart'], 'total'));

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data,
                    borderColor: '#f97316',
                    borderWidth: 2.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#f97316',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: '#1a1a1a',
                        titleColor: '#d1d5db',
                        bodyColor: '#f9fafb',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return fullLabels[context[0].dataIndex];
                            },
                        }
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: labelColor,
                            color: labelColor,
                            font: {
                                size: 11
                            },
                            autoSkip: true,
                            maxTicksLimit: 12,
                            maxRotation: 0,
                        },
                        border: {
                            display: false
                        },
                    },
                    y: {
                        min: 0,
                        max: Math.ceil(Math.max(...data) / 5000) * 5000 || 5000,
                        ticks: {
                            stepSize: Math.ceil(Math.max(...data) / 5 / 1000) * 1000 || 5000,
                            color: labelColor,
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                if (value === 0) return '0';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                return value;
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.15)',
                            borderDash: [4, 4]
                        },
                        border: {
                            display: false
                        },
                    },
                },
            },
            plugins: [{
                id: 'hoverTooltip',
            }]
        });

        // ── Payment Donut Chart ────────────────────────────────────────
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentData = [
            {{ $paymentBreakdown['cash'] }},
            {{ $paymentBreakdown['card'] }},
            {{ $paymentBreakdown['khqr'] }}
        ];
        const paymentColors = ['#a262e0', '#c9a3ec', '#e9d9f8'];

        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'Credit/Debit', 'KHQR'],
                datasets: [{
                    data: paymentData,
                    backgroundColor: paymentColors,
                    borderWidth: 0,
                    cutout: '68%',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 28
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        padding: 8
                    }
                },
            },
            plugins: [{
                id: 'donutLeaderLabels',
                afterDraw(chart) {
                    const {
                        ctx,
                        chartArea
                    } = chart;
                    const meta = chart.getDatasetMeta(0);
                    const cx = (chartArea.left + chartArea.right) / 2;
                    const cy = (chartArea.top + chartArea.bottom) / 2;

                    ctx.save();
                    ctx.font = '1000 15px sans-serif';
                    ctx.fillStyle = isDarkMode ? '#e4e4e7' : '#374151';
                    ctx.strokeStyle = isDarkMode ? '#52525b' : '#9ca3af';
                    ctx.lineWidth = 1;

                    meta.data.forEach((arc, i) => {
                        const angle = (arc.startAngle + arc.endAngle) / 2;
                        const outerR = arc.outerRadius;
                        const startX = cx + Math.cos(angle) * outerR;
                        const startY = cy + Math.sin(angle) * outerR;
                        const midX = cx + Math.cos(angle) * (outerR + 16);
                        const midY = cy + Math.sin(angle) * (outerR + 16);
                        const dir = Math.cos(angle) >= 0 ? 1 : -1;
                        const endX = midX + dir * 14;

                        ctx.beginPath();
                        ctx.moveTo(startX, startY);
                        ctx.lineTo(midX, midY);
                        ctx.lineTo(endX, midY);
                        ctx.stroke();

                        ctx.textAlign = dir === 1 ? 'left' : 'right';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(paymentData[i], endX + dir * 4, midY);
                    });
                },
            }],
        });

    });
</script>
