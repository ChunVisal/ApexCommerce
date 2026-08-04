<script>
    // OUTSIDE userPage() - at the top
    function deleteUser(id, button) {
        if (!confirm('Delete this user?')) return;
        fetch(`/admin/users/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(() => button.closest('tr').remove())
            .catch(err => alert('Error: ' + err.message));
    }

    // Remove toggleAllCheckboxes function - no longer needed

    function updateBulkBar() {
        const count = document.querySelectorAll('.bulk-checkbox:checked').length;
        const bar = document.getElementById('bulkBar');
        const countEl = document.getElementById('bulkCount');
        if (bar) bar.style.display = count > 0 ? 'flex' : 'none';
        if (countEl) countEl.textContent = count;
    }

    function cancelBulkMode() {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
        updateBulkBar();
    }

    function toggleAllCheckboxes(source) {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = source.checked);
        updateBulkBar();
    }

    function bulkDeactivate() {
        const ids = [...document.querySelectorAll('.bulk-checkbox:checked')].map(cb => cb.dataset.id);
        if (!ids.length) return alert('Select users first!');
        if (!confirm(`Deactivate ${ids.length} users?`)) return;
        fetch('/admin/users/bulk-deactivate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ids
                })
            })
            .then(res => res.json())
            .then(() => window.location.reload())
            .catch(err => alert('Error: ' + err.message));
    }

    function bulkDelete() {
        const ids = [...document.querySelectorAll('.bulk-checkbox:checked')].map(cb => cb.dataset.id);
        if (!ids.length) return alert('Select users first!');
        if (!confirm(`Delete ${ids.length} users permanently?`)) return;
        fetch('/admin/users/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ids
                })
            })
            .then(res => res.json())
            .then(() => window.location.reload())
            .catch(err => alert('Error: ' + err.message));
    }

    function userPage() {
        return {
            open: false,
            editMode: false,
            showProfile: false,
            viewMode: 'edit',
            submitting: false,
            avatarPreview: '{{ auth()->user()->avatar }}',

            form: {
                id: null,
                name: '',
                email: '',
                role: '',
                avatar_url: '{{ auth()->user()->avatar }}',
                avatar_file: null,
                password: '',
                password_confirmation: '',
                status: 'active',
                employee_id: '',
                phone: '',
                address: '',
                shift: '',
                pin: '',
                hire_date: '',
                salary: '',
            },

            users: @json($users),
            searchQuery: '',
            roleFilter: 'all',
            statusFilter: 'all',

            getInitials(name) {
                if (!name) return '';

                return name
                    .split(' ')
                    .map(word => word.charAt(0))
                    .join('')
                    .toUpperCase();
            },

            get filteredUsers() {
                let result = [...this.users];

                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(u =>
                        u.name.toLowerCase().includes(q) ||
                        (u.email || '').toLowerCase().includes(q) ||
                        (u.phone || '').toLowerCase().includes(q)
                    );
                }

                if (this.roleFilter && this.roleFilter !== 'all') {
                    result = result.filter(u => u.role === this.roleFilter);
                }

                if (this.statusFilter && this.statusFilter !== 'all') {
                    result = result.filter(u => u.status === this.statusFilter);
                }

                return result;
            },

            openDetail(userId) {
                const user = this.users.find(u => u.id == userId);
                if (!user) return;

                this.viewMode = 'detail';
                this.form = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    role: user.role,
                    avatar: user.avatar,
                    status: user.status,
                    employee_id: user.employee_id,
                    phone: user.phone,
                    address: user.address,
                    shift: user.shift,
                    pin: user.pin,
                    hire_date: user.hire_date,
                    salary: user.salary,
                    last_login: user.last_login,
                    is_online: user.is_online,
                    created_at: user.created_at,
                };
                this.showProfile = true;
                this.open = false;
            },

            emptyForm() {
                return {
                    id: null,
                    name: '',
                    email: '',
                    role: '',
                    avatar_file: null,
                    avatar_url: '',
                    avatar_preview: '',
                    password: '',
                    password_confirmation: '',
                    status: 'active',
                    employee_id: '',
                    phone: '',
                    address: '',
                    shift: '',
                    pin: '',
                    hire_date: '',
                    salary: '',
                };
            },

            handleAvatarFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.form.avatar_file = file;
                this.avatarPreview = URL.createObjectURL(file);
                this.form.avatar_url = '';
            },

            openAdd() {
                this.editMode = false;
                this.form = this.emptyForm();
                this.avatarPreview = '';
                this.open = true;
            },

            openEdit(user) {
                this.editMode = true;
                this.avatarPreview = user.avatar || '';
                this.form = {
                    id: user.id ?? null,
                    name: user.name ?? '',
                    email: user.email ?? '',
                    role: user.role ?? '',
                    avatar_url: user.avatar || '',
                    avatar_file: null,
                    password: '',
                    password_confirmation: '',
                    status: user.status ?? 'active',
                    employee_id: user.employee_id ?? '',
                    phone: user.phone ?? '',
                    address: user.address ?? '',
                    shift: user.shift ?? '',
                    pin: user.pin ?? '',
                    hire_date: user.hire_date ?? '',
                    salary: user.salary ?? '',
                };
                this.open = true;
            },

            closePanel() {
                this.open = false;
                this.showProfile = false;
                this.viewMode = 'edit';
            },
            submitForm() {
                if (!this.form.name || !this.form.email || !this.form.role) {
                    alert('Please fill all required fields');
                    return;
                }

                if (!this.editMode && !this.form.password) {
                    alert('Password is required for new users');
                    return;
                }

                if (!this.form.email.includes('@')) {
                    alert('Please enter a valid email address');
                    return;
                }

                if (this.submitting) return;
                this.submitting = true;

                const isEdit = this.editMode;
                const url = isEdit ? `/admin/users/${this.form.id}` : '/admin/users';

                console.log('avatar_file:', this.form.avatar_file);
                console.log('avatar_url:', this.form.avatar_url);
                // Use FormData for file upload
                const fd = new FormData();
                fd.append('name', this.form.name);
                fd.append('email', this.form.email);
                fd.append('role', this.form.role);
                fd.append('password', this.form.password || '');
                fd.append('password_confirmation', this.form.password_confirmation || '');
                fd.append('status', this.form.status);
                fd.append('phone', this.form.phone || '');
                fd.append('address', this.form.address || '');

                // Only append cashier fields if role is cashier
                if (this.form.role === 'cashier') {
                    fd.append('employee_id', this.form.employee_id || '');
                    fd.append('shift', this.form.shift || '');
                    fd.append('hire_date', this.form.hire_date || '');
                    fd.append('salary', this.form.salary || '');
                    fd.append('pin', this.form.pin || '');
                }

                if (this.form.avatar_file) {
                    fd.append('avatar_file', this.form.avatar_file);
                } else if (this.form.avatar_url) {
                    fd.append('avatar_url', this.form.avatar_url);
                }

                if (isEdit) fd.append('_method', 'PUT');

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: fd,
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.errors) {
                            const messages = Object.values(data.errors).flat().join('\n');
                            alert('Error:\n' + messages);
                            this.submitting = false;
                        } else {
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                        this.submitting = false;
                    });
            },
        }
    }
</script>
