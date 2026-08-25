<script>
    function notificationPage() {
        return {
            returnOpen: false,
            returnForm: {
                request_id: null,
                product_id: null,
                product_name: '',
                quantity: 1,
                maxQuantity: 1,
                reason: ''
            },

            perPage: {{ $perPage ?? 1 }},
            step: 3,
            loading: false,
            loadMore() {
                this.loading = true;
                fetch(`{{ route('cashier.notifications') }}?per_page=${this.perPage + this.step}&ajax=1`, {
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
        };
    }
</script>
