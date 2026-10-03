<script>
    function movementBadge() {
        return {
            stockCount: {{ $unseenStockMovements }}, // starting value from page load
            financialCount: {{ $unseenFinancialMovements }}, // starting value from page load
            interval: null,
            init() {
                setInterval(() => {
                    fetch('/admin/inventory/stock-movements/count')
                        .then(res => res.json())
                        .then(data => {
                            this.stockCount = data.count;
                        });
                    fetch('/admin/inventory/financial-movements/count')
                        .then(res => res.json())
                        .then(data => {
                            this.financialCount = data.count;
                        });
                }, 10000);
            },
            destroy() {
                clearInterval(this.interval);
            }
        };
    }
</script>
