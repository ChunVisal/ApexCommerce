<script>
    function reportsPage() {
        return withPagination({
            headers: @json($headers),
            dailySales: @json($dailySales),
            topCashiers: @json($topCashiers),
            orders: @json($orders),

            tab: localStorage.getItem('reportsTab') || 'daily',

            get filteredRecords() {
                if (this.tab === 'daily') return this.dailySales;
                if (this.tab === 'cashiers') return this.topCashiers;
                if (this.tab === 'orders') return this.orders;
                return [];
            },

            get paginatedDailySales() {
                return this.pageOf(this.dailySales, 'daily');
            },
            get paginatedTopCashiers() {
                return this.pageOf(this.topCashiers, 'cashiers');
            },
            get paginatedOrders() {
                return this.pageOf(this.orders, 'orders');
            },

            pageOf(list, tabName) {
                if (this.tab !== tabName) return list;
                const start = (this.currentPage - 1) * this.perPage;
                return list.slice(start, start + this.perPage);
            },

            setTab(value) {
                this.tab = value;
                localStorage.setItem('reportsTab', value);
            },


        });
    }
</script>
