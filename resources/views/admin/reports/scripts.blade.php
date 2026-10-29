<script>
    function reportsPage() {
        return {
            headers: @json($headers),
            dailySales: @json($dailySales),
            topCashiers: @json($topCashiers),
            orders: @json($orders),

            tab: localStorage.getItem('reportsTab') || 'daily',

            setTab(value) {
                this.tab = value;
                localStorage.setItem('reportsTab', value);
            },
        }
    }
</script>
