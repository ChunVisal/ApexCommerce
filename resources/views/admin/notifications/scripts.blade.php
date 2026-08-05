<script>
    function notificationPage() {
        return {
            perPage: {{ $perPage ?? 1 }},
            step: 3,
            loading: false,
            loadMore() {
                this.loading = true;
                fetch(`{{ route('admin.notifications') }}?per_page=${this.perPage + this.step}&ajax=1`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('notificationGroups').innerHTML = html;
                        this.perPage += this.step;
                        this.loading = false;
                    });
            },
        }
    }
</script>
