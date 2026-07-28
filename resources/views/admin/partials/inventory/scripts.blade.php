<script>
    function inventoryPage() {


        return {

            open: false,
            submitting: false,
            stockMap: {{ \Illuminate\Support\Js::from($products->pluck('stock_quantity', 'code')) }},
            thresholdMap: {{ \Illuminate\Support\Js::from($products->pluck('low_stock_threshold', 'code')) }},

            stockDropOpen: false,
            dropForm: {
                product_id: null,
                product_name: '',
                current_stock: 0,
                cashier_id: '',
                quantity: 1
            },
            cashierStocks: @json($cashierStocks ?? []),

            searchQuery: '',
            categoryFilter: '',
            statusFilter: 'all',
            stockFilter: 'all',
            products: @json($products),

            form: {
                product_code: '',
                type: 'in',
                quantity: null,
                low_stock_threshold: null,
                reason: '',
                notes: '',
                status: 'active',
            },

            // search product dropstock 
            cashierOpen: false,
            selectedCashierName: '',
            cashiers: @json($cashiers ?? []),


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


            get currentStock() {
                return this.form.product_code ? (this.stockMap[this.form.product_code] ?? null) : null;
            },


            get currentThreshold() {
                return this.form.product_code ? (this.thresholdMap[this.form.product_code] ?? null) : null;
            },

            openAdjust(item = null) {
                this.form = {
                    product_code: item ? item.code : '',
                    type: 'in',
                    quantity: null,
                    low_stock_threshold: item ? item.low_stock_threshold : null,
                    reason: '',
                    notes: '',
                    status: item ? item.status : 'active',
                };
                this.open = true;
            },

            closePanel() {
                this.open = false;
            },

            openStockDrop(productId) {
                const product = this.products.find(p => p.id == productId);
                if (!product) return;

                this.dropForm = {
                    product_id: product.id,
                    product_name: product.name,
                    current_stock: product.stock_quantity,
                    cashier_id: '',
                    quantity: 1,
                };
                this.stockDropOpen = true;
            },
            getCashierStock() {
                const stock = this.cashierStocks.find(s => s.product_id === this.dropForm.product_id && s.cashier_id ===
                    this.dropForm.cashier_id);
                return stock ? stock.allocated_quantity - stock.sold_quantity : 0;
            },

            openStockDropFromRequest(requestId, productId, cashierId, quantity) {
                const product = this.products.find(p => p.id == productId);
                if (!product) return;

                this.dropForm = {
                    request_id: requestId,
                    product_id: productId,
                    product_name: product.name,
                    current_stock: product.stock_quantity,
                    cashier_id: cashierId,
                    quantity: quantity,
                };
                this.stockDropOpen = true;
            },

            submitDrop() {
                fetch('/admin/inventory/stock-drop', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.dropForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Also approve the request
                            if (this.dropForm.request_id) {
                                fetch('/admin/notifications/' + this.dropForm.request_id + '/approve', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                });
                            }
                            window.location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            },

            submitForm() {
                if (this.submitting) return;

                if (!this.form.product_code) {
                    alert('Please select a product!');
                    return;
                }
                if (!this.form.reason) {
                    alert('Please select a reason!');
                    return;
                }

                // Default quantity to 0 if empty
                if (!this.form.quantity || this.form.quantity === '') {
                    this.form.quantity = 0;
                }

                this.submitting = true;

                fetch('{{ route('admin.inventory.adjust') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.form)
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
                            alert(data.error || 'Something went wrong');
                            return;
                        }
                        this.stockMap[this.form.product_code] = data.new_stock;
                        this.closePanel();
                        window.location.reload();
                    })
                    .catch(err => {
                        this.submitting = false;
                        alert('Error: ' + err.message);
                    });
            },
        }
    }
</script>
