<script>
    function productPage() {
        return {

            searchQuery: '',
            filterCategory: '',
            filterStock: '',
            products: @json($products),

            viewMode: localStorage.getItem('viewMode') || 'list',

            // new product request
            requestOpen: false,
            newProductList: [{
                name: '',
                quantity: 1,
                note: ''
            }],

            // low stock request
            restockOpen: false,
            requestSearch: '',
            requestForm: {
                product_id: '',
                product_name: '',
                quantity: 1,
                note: ''
            },

            returnOpen: false,
            returnForm: {
                isReturnToWarehouse: false,
                request_id: null,
                product_id: null,
                product_name: '',
                quantity: 1,
                maxQuantity: 1,
                reason: '',
            },

            // searching all products 
            allProducts: @json($allProducts),
            selectedProductName: '',

            get filteredProducts() {
                let result = [...this.products];

                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(p => p.name.toLowerCase().includes(q) || (p.code || '').toLowerCase()
                        .includes(q));
                }
                if (this.filterCategory) {
                    result = result.filter(p => p.category_name === this.filterCategory);
                }
                if (this.filterStock === 'in') result = result.filter(p => p.remaining > (p.low_stock_threshold ||
                    5));
                else if (this.filterStock === 'low') result = result.filter(p => p.remaining > 0 && p.remaining <= (
                    p.low_stock_threshold || 5));
                else if (this.filterStock === 'out') result = result.filter(p => p.remaining <= 0);

                return result;
            },

            get filteredRequestProducts() {
                const q = (this.requestSearch || '').toLowerCase();
                return this.products.filter(p =>
                    (p.remaining || 0) <= (p.low_stock_threshold || 5) && (!q || p.name.toLowerCase().includes(
                        q)));
            },

            submitRequest() {
                if (!this.requestForm.product_id && !this.requestForm.product_name) {
                    alert('Please select a product or type a name');
                    return;
                }
                fetch('/cashier/stock-request', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.requestForm)
                    })
                    .then(res => res.json().then(data => ({
                        ok: res.ok,
                        data
                    })))
                    .then(({
                        ok,
                        data
                    }) => {
                        if (!ok) {
                            this.$dispatch('toast', {
                                message: data.message || 'Something went wrong',
                                type: 'error'
                            });
                            return;
                        }
                        this.$dispatch('toast', {
                            message: data.message,
                            type: 'success'
                        });
                        this.restockOpen = false;
                        this.requestForm = {
                            product_id: '',
                            product_name: '',
                            quantity: 1,
                            note: ''
                        }; // ADDED — reset form
                    })
                    .catch(err => {
                        this.$dispatch('toast', {
                            message: 'Network error: ' + err.message,
                            type: 'error'
                        });
                    });
            },

            addNewProductItem() {
                this.newProductList.push({
                    product_id: '',
                    name: '',
                    quantity: 1,
                    note: ''
                });
            },

            submitNewProductRequest() {
                const validItems = this.newProductList.filter(item => item.product_id || item.name.trim());
                if (validItems.length === 0) {
                    this.$dispatch('toast', {
                        message: 'Add at least one product',
                        type: 'error'
                    });
                    return;
                }
                fetch('/cashier/stock-request/bulk', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            items: validItems
                        })
                    })
                    .then(res => res.json().then(data => ({
                        ok: res.ok,
                        data
                    })))
                    .then(({
                        ok,
                        data
                    }) => {
                        if (!ok) {
                            this.$dispatch('toast', {
                                message: data.message || 'Something went wrong',
                                type: 'error'
                            });
                            return;
                        }
                        this.$dispatch('toast', {
                            message: data.message,
                            type: 'success'
                        });
                        this.requestOpen = false;
                        this.newProductList = [{
                            name: '',
                            quantity: 1,
                            note: ''
                        }]; // reset THIS form, not returnForm
                    })
                    .catch(err => {
                        this.$dispatch('toast', {
                            message: 'Network error: ' + err.message,
                            type: 'error'
                        });
                    });
            },

            reportLoss(productId, productName, maxQty) {
                this.returnForm = {
                    isReturnToWarehouse: false,
                    request_id: null,
                    product_id: productId,
                    product_name: productName,
                    quantity: 1,
                    maxQuantity: maxQty,
                    reason: '',
                };
                this.returnOpen = true;
            },

            submitReturn() {
                if (this.returnForm.quantity > this.returnForm.maxQuantity) {
                    this.$dispatch('toast', {
                        message: 'Cannot report more than available: ' + this.returnForm.maxQuantity,
                        type: 'error'
                    });
                    return;
                }

                const isReturn = this.returnForm.isReturnToWarehouse;
                const url = isReturn ? '/cashier/stock-return' : '/cashier/stock-loss';

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.returnForm)
                    })
                    .then(res => res.json().then(data => ({
                        ok: res.ok,
                        data
                    })))
                    .then(({
                        ok,
                        data
                    }) => {
                        if (!ok) {
                            this.$dispatch('toast', {
                                message: data.message || 'Something went wrong',
                                type: 'error'
                            });
                            return;
                        }
                        this.$dispatch('toast', {
                            message: data.message,
                            type: 'success'
                        });
                        this.returnOpen = false;

                        const product = this.products.find(p => p.id === this.returnForm.product_id);
                        if (product) {
                            if (isReturn) {
                                product.allocated_quantity = (product.allocated_quantity || 0) - this.returnForm.quantity;
                            } else {
                                product.lost = (product.lost || 0) + this.returnForm.quantity;
                            }
                            product.remaining = product.allocated - product.sold - product.lost;
                        }

                        this.returnForm = {
                            request_id: null,
                            product_id: null,
                            product_name: '',
                            quantity: 1,
                            maxQuantity: 1,
                            reason: ''
                        }; // reset
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
