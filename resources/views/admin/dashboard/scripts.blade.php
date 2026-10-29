<script>
    function dashboardPage() {
        return {
            headers: @json($headers),
            topProducts: @json($topProducts),
            topCategories: @json($topCategories),
        }
    }
</script>
