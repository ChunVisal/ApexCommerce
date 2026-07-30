<script>
    function activityPage() {
        return {
            searchQuery: '',
            filterUser: '',
            filterPage: '',
            filterType: '',
            activities: @json($activities),

            get filteredActivities() {
                let result = [...this.activities];
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(a => a.description.toLowerCase().includes(q));
                }
                if (this.filterUser) {
                    result = result.filter(a => a.user_id == this.filterUser);
                }
                if (this.filterPage) {
                    result = result.filter(a => a.page == this.filterPage);
                }
                if (this.filterType) {
                    result = result.filter(a => a.status == this.filterType);
                }
                return result;
            },
        }

    }
</script>
