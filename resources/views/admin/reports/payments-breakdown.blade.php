<div
    class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm p-5 overflow-hidden border border-gray-200 dark:border-zinc-800/50">

    <div class="flex flex-col lg:flex-row gap-4">

        {{-- ── Payment Breakdown (donut chart) ── --}}
        <div class="flex-1">
            <h3 class="text-[15px] font-semibold text-gray-800 dark:text-zinc-100">Payment</h3>


            <div class="flex justify-start items-center">
                <div class="flex flex-col items-start gap-4 mt-2">
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
                <div class="flex items-start justify-start relative" style="height: 200px;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="flex-1">
            <div class="grid grid-cols-2 gap-4 h-full">
                @foreach ($paymentSummary as $card)
                    <div
                        class="flex flex-col bg-gray-50 dark:bg-zinc-800/30 rounded-md border border-gray-200/60 dark:border-zinc-800/70 p-3">
                        <div>
                            <h3 class="text-xs uppercase font-medium text-gray-500 dark:text-zinc-300 mb-1">
                                {{ $card['title'] }}
                            </h3>
                            <p class="text-xl font-bold {{ $card['valueColor'] }}">
                                {{ $card['value'] }}
                            </p>
                        </div>
                        <p class="mt-auto text-[11px] text-gray-500 dark:text-zinc-300">
                            {{ $card['subtitle'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    #salesTooltip {
        transition: opacity 0.15s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const labelColor = '#787878';
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
                    ctx.font = '1000 17px sans-serif';
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
