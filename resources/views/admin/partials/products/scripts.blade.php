<script>
    // ── Filter table ──────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {

        $(document).ready(function() {
            let searchTimer;

            $('#search').on('input', function() {
                clearTimeout(searchTimer);
                const query = $(this).val();
                $('#clearSearch').toggle(query.length > 0);

                searchTimer = setTimeout(function() {
                    $.get('{{ route('admin.products') }}', {
                        search: query,
                        ajax: 1
                    }, function(data) {
                        const alpine = document.querySelector(
                            '[x-data="productPage()"]').__x.$data;
                        alpine.products = data.products;
                    });
                }, 400);
            });

            $('#clearSearch').on('click', function() {
                $('#search').val('').trigger('input');
            });
        });

        var emptyRow = document.getElementById('noCategoryRow');
        var emptyGrid = document.getElementById('noFilterResultsGrid');
    });

    function deleteProduct(id, button) {
        if (button.dataset.hasOrders === '1') {
            alert('Cannot delete: This product has existing orders');
            return;
        }
        if (button.dataset.hasStock === '1') {
            alert('Cannot delete: This product has stock movement history');
            return;
        }

        if (!confirm('Are you sure you want to delete this product?')) return;

        fetch('/admin/products/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (res.status === 422) {
                    return res.json().then(data => {
                        alert(data.message);
                        return;
                    });
                }
                window.location.reload();
                if (!res.ok) throw new Error('Error');
            })
            .catch(err => alert('Error: ' + err.message));
    }

    // Toggle all checkboxes - NOW shows/hides checkboxes and trash
    function toggleAllCheckboxes(source) {
        const isBulkMode = source.checked;

        // Show/hide checkboxes and trash buttons in each row
        document.querySelectorAll('tbody tr').forEach(row => {
            const trashBtn = row.querySelector('.trash-btn');
            const checkbox = row.querySelector('.bulk-checkbox');

            if (trashBtn) {
                if (isBulkMode) {
                    trashBtn.style.display = 'none'; // Hide trash
                    checkbox.checked = true; // Check it
                } else {
                    trashBtn.style.display = ''; // Show trash
                    checkbox.checked = false; // Uncheck it
                }
            }
        });

        updateBulkBar();
    }

    // Update bulk bar visibility
    function updateBulkBar() {
        const checkedBoxes = document.querySelectorAll('.bulk-checkbox:checked');
        const count = checkedBoxes.length;
        const bar = document.getElementById('bulkBar');
        const countEl = document.getElementById('bulkCount');
        const selectAll = document.getElementById('selectAll');

        if (bar) {
            bar.style.display = count > 0 ? 'flex' : 'none';
        }
        if (countEl) {
            countEl.textContent = count;
        }
    }

    // Cancel bulk mode - reset everything
    function cancelBulkMode() {
        // Uncheck select all
        const selectAll = document.getElementById('selectAll');
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
        if (selectAll) selectAll.checked = false;

        // Show trash, hide checkboxes
        document.querySelectorAll('tbody tr').forEach(row => {
            const trashBtn = row.querySelector('.trash-btn');

            if (trashBtn) {
                trashBtn.style.display = '';
            }
        });

        updateBulkBar();
    }

    function bulkDelete() {
        const checkedBoxes = document.querySelectorAll('.bulk-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.dataset.id);

        if (!ids.length) {
            alert('Select products first!');
            return;
        }

        // Check if any selected products have relationships
        for (let cb of checkedBoxes) {
            if (cb.dataset.hasOrders === '1' || cb.dataset.hasStock === '1') {
                alert('Some selected products have existing orders or stock history and cannot be deleted.');
                return;
            }
        }

        if (!confirm(`Delete ${ids.length} products?`)) return;

        fetch('/admin/products/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: ids
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                window.location.reload();
            })
            .catch(err => alert('Error: ' + err.message));
    }

    function productPage() {

        let _isSubmitting = false;

        return {
            viewMode: localStorage.getItem('productViewMode') || 'table',
            categoryProducts: [],
            search: '',
            open: false,
            draftList: [],
            addingToDraft: false,
            draftEditIndex: null,
            editMode: false,
            submitting: false,
            selectedCategoryId: null,
            pendingName: '',
            selectedProductName: '',

            // search query
            products: @json($products ?? []),
            searchQuery: '',
            categoryFilter: '',
            statusFilter: 'all',
            stockFilter: 'all',

            get filteredProducts() {
                let result = [...this.products];

                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.code || '').toLowerCase().includes(q) ||
                        (p.barcode || '').toLowerCase().includes(q) ||
                        (p.category?.name || '').toLowerCase().includes(q)
                    );
                }

                if (this.categoryFilter) {
                    result = result.filter(p => p.category?.name === this.categoryFilter);
                }

                if (this.statusFilter && this.statusFilter !== 'all') {
                    result = result.filter(p => p.status === this.statusFilter.toLowerCase());
                }

                if (this.stockFilter === 'out') {
                    result = result.filter(p => p.stock_quantity <= 0);
                } else if (this.stockFilter === 'low') {
                    result = result.filter(p => p.stock_quantity > 0 && p.stock_quantity <= (p
                        .low_stock_threshold || 5));
                } else if (this.stockFilter === 'in') {
                    result = result.filter(p => p.stock_quantity > (p.low_stock_threshold || 5));
                }

                return result;
            },

            form: {
                id: null,
                code: '',
                name: '',
                category_code: '',
                barcode: '',
                price: null,
                stock: null,
                status: 'active',
                image_url: '',
                image_file: null,
                image_preview: '',

            },

            emptyForm() {
                return {
                    id: null,
                    code: '',
                    name: '',
                    selectedProductName: '',
                    category_code: '',
                    barcode: '',
                    price: null,
                    stock: null,
                    status: 'active',
                    image_url: '',
                    image_file: null,
                    image_preview: '',
                };
            },

            handleImageFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.form.image_file = file;
                this.form.image_preview = URL.createObjectURL(file);
                this.form.image_url = '';
            },

            clearSearch() {
                this.search = '';
            },

            openAdd() {
                _isSubmitting = false;
                this.editMode = false;
                this.submitting = false;
                this.categoryProducts = [];
                this.selectedCategoryId = null;
                this.addingToDraft = false;
                this.pendingName = '';
                this.form = this.emptyForm();
                this.draftList = [];
                this.draftEditIndex = null;
                this.open = true;
            },

            openEdit(product) {
                this.editMode = true;
                this.submitting = false;
                this.categoryProducts = [];
                this.selectedCategoryId = product.category_id ?? null;

                const original = {
                    id: product.id ?? null,
                    code: product.code ?? '',
                    name: product.name ?? '',
                    category_code: product.category?.code ?? '',
                    barcode: product.barcode ?? '',
                    price: product.selling_price ?? null,
                    stock: product.stock_quantity ?? null,
                    status: product.status ?? 'active',
                    image_url: product.image ?? '',
                    image_file: null,
                    image_preview: '',
                };

                const existsInDB = this.categoryProducts.find(p => p.name === this.form.name && p.id !== null);
                if (existsInDB && this.draftEditIndex === null) {
                    alert(
                        `"${this.form.name}" already exists in database.`);
                    this.addingToDraft = false;
                    return;
                }

                this.form = {
                    ...original
                };
                this.pendingName = '';

                if (this.form.category_code) {
                    fetch(`/admin/products/by-category?category_code=${this.form.category_code}`)
                        .then(res => res.json())
                        .then(data => {
                            this.categoryProducts = Array.isArray(data.products) ? data.products : [];
                            if (!this.categoryProducts.some(p => p.name === original.name)) {
                                this.categoryProducts.push({
                                    name: original.name
                                });
                            }
                            this.form.name = original.name;
                            this.open = true;
                        })
                        .catch(() => {
                            this.form.name = original.name;
                            this.open = true;
                        });
                } else {
                    this.open = true;
                }
            },

            addToDraft() {

                console.log('addToDraft called, addingToDraft:', this.addingToDraft);
                // ← ADD HERE
                const existsInDB = this.categoryProducts.find(p => p.name === this.form.name && p.id !== null);
                if (existsInDB && this.draftEditIndex === null) {
                    alert(
                        `"${this.form.name}" already exists in database.`);
                    this.addingToDraft = false;
                    return;
                }

                if (this.addingToDraft) return;
                this.addingToDraft = true;


                if (!this.form.name) {
                    alert('Please select a product!');
                    this.addingToDraft = false;
                    return;
                }

                if (this.draftEditIndex === null && this.draftList.some(item => item.name === this.form.name)) {
                    alert('This product is already in your draft!');
                    this.addingToDraft = false;
                    return;
                }

                const draftItem = {
                    _id: Math.random().toString(36).substr(2, 9), // ← better unique id
                    name: this.form.name,
                    category_code: this.form.category_code,
                    category_id: this.selectedCategoryId,
                    price: this.form.price,
                    stock: this.form.stock ?? 0,
                    status: this.form.status,
                    image_url: this.form.image_url,
                    image_file: this.form.image_file,
                    image_preview: this.form.image_preview,
                };

                if (this.draftEditIndex !== null) {
                    const newList = [...this.draftList];
                    newList[this.draftEditIndex] = draftItem;
                    this.draftList = newList;
                    this.draftEditIndex = null;
                } else {
                    this.draftList = [...this.draftList, draftItem];
                }

                const keepCategory = this.form.category_code;
                const keepCategoryId = this.selectedCategoryId;
                this.form = this.emptyForm();
                this.form.category_code = keepCategory;
                this.selectedCategoryId = keepCategoryId;

                this.$nextTick(() => {
                    this.addingToDraft = false;
                });
            },

            editDraft(index) {
                const item = this.draftList[index];
                this.draftEditIndex = index;
                this.form.category_code = item.category_code;
                this.form.price = item.price;
                this.form.stock = item.stock;
                this.form.status = item.status;
               
                this.form.image_url = item.image_url ?? ''; // ← ADD
                this.form.image_preview = item.image_preview ?? ''; // ← ADD
                this.form.image_file = item.image_file ?? null; // ← ADD
                this.selectedCategoryId = item.category_id;

                fetch(`/admin/products/by-category?category_code=${item.category_code}`)
                    .then(res => res.json())
                    .then(data => {
                        this.categoryProducts = Array.isArray(data.products) ? data.products : [];
                        this.$nextTick(() => {
                            this.form.name = item.name;
                        });
                    });
            },

            removeDraft(index) {
                const newList = [...this.draftList];
                newList.splice(index, 1);
                this.draftList = newList;
            },

            closePanel() {
                this.open = false;
            },


            loadProducts() {
                this.form.name = '';
                this.selectedCategoryId = null;
                if (this._loadingProducts) return;
                if (!this.form.category_code) return;

                fetch(`/admin/products/by-category?category_code=${this.form.category_code}`)
                    .then(res => res.json())
                    .then(data => {
                        this.categoryProducts = Array.isArray(data.products) ? data.products : [];
                        this.selectedCategoryId = data.category_id ?? null;
                        this._loadingProducts = false;
                    })
                    .catch(() => {
                        this.categoryProducts = [];
                        this._loadingProducts = false;
                    });
            },

            autoFillDetails() {
                const selected = this.categoryProducts.find(p => p.name === this.form.name);
                if (!selected) return;
                this.form.price = selected.selling_price ?? null;
            },

            async submitAllDrafts() {
                if (_isSubmitting) return;
                _isSubmitting = true;
                this.submitting = true;

                for (const item of this.draftList) {
                    const fd = new FormData();
                    fd.append('name', item.name);
                    fd.append('category_id', item.category_id);
                    fd.append('selling_price', item.price);
                    fd.append('stock_quantity', item.stock);
                    fd.append('status', item.status);
                    if (item.image_file) fd.append('image_file', item.image_file);
                    else if (item.image_url) fd.append('image_url', item.image_url);

                    try {
                        await fetch('/admin/products', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: fd
                        });
                    } catch (e) {
                        console.error(e);
                    }
                }

                this.submitting = false;
                this.draftList = [];
                _isSubmitting = false;
                window.location.reload();
            },

            submitForm() {

                if (!this.editMode) {
                    const existsInDB = this.categoryProducts.find(p => p.name === this.form.name && p.id !== null);
                    if (existsInDB && this.draftEditIndex === null) {
                        alert(`"${this.form.name}" already exists in database.`);
                        this.addingToDraft = false;
                        return;
                    }
                }

                if (_isSubmitting) {
                    console.log('Blocked duplicate submit');
                    return;
                }

                if (!this.editMode && this.draftList.length > 0) {
                    this.submitAllDrafts();
                    return;
                }

                _isSubmitting = true;
                this.submitting = true;

                const url = this.editMode ? `/admin/products/${this.form.id}` : `/admin/products`;

                const fd = new FormData();
                fd.append('name', this.form.name);
                fd.append('category_id', this.selectedCategoryId);
                fd.append('selling_price', this.form.price);
                fd.append('stock_quantity', this.form.stock);
                fd.append('status', this.form.status);

                if (this.form.image_file) {
                    fd.append('image_file', this.form.image_file);
                } else if (this.form.image_url) {
                    fd.append('image_url', this.form.image_url);
                }

                if (this.editMode) fd.append('_method', 'PUT');

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                    })
                    .then(res => {
                        if (!res.ok) return res.text().then(t => {
                            throw new Error(t);
                        });
                        return res.json();
                    })
                    .then(data => {
                        if (data && data.id) {
                            _isSubmitting = false;
                            this.submitting = false;
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        _isSubmitting = false;
                        this.submitting = false;
                        alert('Error: ' + err.message);
                    });
            },

        };
    }
</script>
