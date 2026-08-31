<script>
    function movementBadge() {
        return {
            count: {{ $unseenMovements }}, // starting value from page load
            interval: null,
            init() {
                setInterval(() => {
                    fetch('/admin/inventory/movements/count')
                        .then(res => res.json())
                        .then(data => {
                            this.count = data.count;
                        });
                }, 10000);
            },
            destroy() {
                clearInterval(this.interval);
            }
        };
    }
</script>
