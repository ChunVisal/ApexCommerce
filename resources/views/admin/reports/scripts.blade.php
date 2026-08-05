<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDarkMode = document.documentElement.classList.contains('dark');

        // ---------- TAB SWITCHING ----------
        const tabs = document.querySelectorAll('.report-tab');
        const panels = document.querySelectorAll('.report-panel');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => {
                    t.classList.remove('text-[#0F6E8C]', 'border-[#0F6E8C]',
                        'font-semibold');
                    t.classList.add('text-gray-500', 'dark:text-zinc-400',
                        'font-medium', 'border-transparent');
                });
                tab.classList.add('text-[#0F6E8C]', 'border-[#0F6E8C]', 'font-semibold');
                tab.classList.remove('text-gray-500', 'dark:text-zinc-400', 'font-medium',
                    'border-transparent');

                panels.forEach(p => p.classList.add('hidden'));
                document.getElementById('tab-' + tab.dataset.tab).classList.remove('hidden');
            });
        });

        // ---------- REVENUE TREND CHART ----------
        const revenueCanvas = document.getElementById('revenueTrendChart');
        const existing = Chart.getChart(revenueCanvas);
        if (existing) existing.destroy();

        const ctx = revenueCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(15, 110, 140, 0.25)');
        gradient.addColorStop(1, 'rgba(15, 110, 140, 0)');

        const labelColor = isDarkMode ? '#787878' : '#9ca3af';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trend['labels']),
                datasets: [{
                    label: 'Revenue',
                    data: @json($trend['revenue']),
                    borderColor: '#0F6E8C',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#0F6E8C',
                    pointBorderColor: isDarkMode ? '#18181b' : '#fff',
                    pointBorderWidth: 1.5,
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
                        callbacks: {
                            label: (ctx) => '$' + ctx.parsed.y.toLocaleString()
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
                            color: labelColor,
                            font: {
                                size: 11
                            },
                            callback: (val) => '$' + (val / 1000) + 'k',
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.15)',
                            borderDash: [4, 4]
                        },
                        border: {
                            display: false
                        },
                    }
                }
            }
        });

        // ---------- PAYMENT REPORT DONUT CHART ----------
        const paymentCanvas = document.getElementById('paymentReportChart');
        const existingPayment = Chart.getChart(paymentCanvas);
        if (existingPayment) existingPayment.destroy();

        new Chart(paymentCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json(collect($payments)->pluck('method')),
                datasets: [{
                    data: @json(collect($payments)->pluck('amount')),
                    backgroundColor: @json(collect($payments)->pluck('color')),
                    borderWidth: 0,
                    cutout: '68%',
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
                        callbacks: {
                            label: (ctx) => ctx.label + ': $' + ctx.parsed.toLocaleString()
                        }
                    },
                }
            }
        });
    });
</script>
