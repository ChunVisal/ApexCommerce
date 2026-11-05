<script>
    function dashboardPage() {
        return withPagination({
            headers: @json($headers),
            topProducts: @json($topProducts),
            topCategories: @json($topCategories),

            get filteredRecords() {
                return [];
            },
        });
    }
</script>
