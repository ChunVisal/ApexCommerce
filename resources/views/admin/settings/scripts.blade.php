<script>
    function settingsPage() {
        return {
            activeTab: 'general',
            logoPreview: '{{ App\Models\Setting::get('logo') }}',

            submitSettings(event) {
                this.submitting = true;

                const formData = new FormData(event.target);

                fetch('{{ route('admin.settings.save') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => res.json().then(data => ({
                        ok: res.ok,
                        data
                    })))
                    .then(({
                        ok,
                        data
                    }) => {
                        this.submitting = false;

                        if (!ok) {
                            this.$dispatch('toast', {
                                message: data.message || 'Failed to save settings',
                                type: 'error'
                            });
                            return;
                        }

                        this.$dispatch('toast', {
                            message: data.message || 'Settings saved successfully',
                            type: 'success'
                        });
                    })
                    .catch(err => {
                        this.submitting = false;
                        this.$dispatch('toast', {
                            message: 'Network error: ' + err.message,
                            type: 'error'
                        });
                    });
            },
        }
    }
</script>
