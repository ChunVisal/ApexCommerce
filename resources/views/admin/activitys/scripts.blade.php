<script>
    function clearLogs() {
        const days = prompt('Delete logs older than how many days? (Leave empty to clear ALL)', '30');
        if (days === null) return; // Cancelled

        const message = days ? `Clear logs older than ${days} days?` :
        'Clear ALL activity logs? This cannot be undone.';

        if (!confirm(message)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.activitylog.clear') }}';
        form.innerHTML = `
        @csrf
        <input type="hidden" name="days" value="${days}">
    `;
        document.body.appendChild(form);
        form.submit();
    }

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
