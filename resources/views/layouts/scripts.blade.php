<script>
    function movementBadge() {
        return {
            count: {{ $unseenMovements }}, // starting value from page load
            init() {
                setInterval(() => {
                    fetch('/admin/inventory/movements/count')
                        .then(res => res.json())
                        .then(data => {
                            this.count = data.count;
                        });
                }, 2000);
            }
        };
    }
</script>
