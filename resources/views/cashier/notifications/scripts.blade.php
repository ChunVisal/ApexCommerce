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

            returnStock(requestId) {
                const notif = @json($notifications).find(n => n.id === requestId);
                if (!notif) return;

                this.returnForm = {
                    request_id: requestId,
                    product_id: notif.product_id,
                    product_name: notif.product?.name || notif.product_name,
                    quantity: 1,
                    maxQuantity: notif.quantity_approved || notif.quantity_requested,
                    reason: '',
                };
                this.returnOpen = true;
            },

            submitReturn() {
                if (this.returnForm.quantity > this.returnForm.maxQuantity) {
                    alert('Cannot report more than received. Max: ' + this.returnForm.maxQuantity);
                    return;
                }

                fetch('/cashier/stock-return', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.returnForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        this.returnOpen = false;
                        window.location.reload();
                    });
            },
        };
    }
</script>
