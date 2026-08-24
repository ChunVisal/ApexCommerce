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

            approveRequest(id, event) {
                const formData = new FormData(event.target);

                fetch(`/admin/notifications/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            this.$dispatch('toast', {
                                message: data.message || 'Approval failed',
                                type: 'error'
                            });
                            return;
                        }

                        this.$dispatch('toast', {
                            message: data.message,
                            type: 'success'
                        });

                        document
                            .querySelector(`[data-request-id="${id}"]`)
                            ?.remove();
                    })
                    .catch(err => {
                        this.$dispatch('toast', {
                            message: 'Network error: ' + err.message,
                            type: 'error'
                        });
                    });
            },

            rejectRequest(id, event) {
                const formData = new FormData(event.target);

                fetch(`/admin/notifications/${id}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            this.$dispatch('toast', {
                                message: data.message || 'Rejection failed',
                                type: 'error'
                            });
                            return;
                        }

                        this.$dispatch('toast', {
                            message: data.message,
                            type: 'success'
                        });

                        document
                            .querySelector(`[data-request-id="${id}"]`)
                            ?.remove();
                    })
                    .catch(err => {
                        this.$dispatch('toast', {
                            message: 'Network error: ' + err.message,
                            type: 'error'
                        });
                    });
            },
        }
    }
</script>
