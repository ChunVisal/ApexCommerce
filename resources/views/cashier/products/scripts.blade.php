<script>
    function productPage() {
        return {

            searchQuery: '',
            filterCategory: '',
            filterStock: '',
            products: @json($products),

            viewMode: 'list',

            // new product request
            requestNewProduct: false,
            newProductList: [{
                name: '',
                quantity: 1,
                note: ''
            }],

            // low stock request
            requestOpen: false,
            requestSearch: '',
            requestForm: {
                product_id: '',
                product_name: '',
                quantity: 1,
                note: ''
            },

            returnOpen: false,
            returnForm: {
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
                if (this.filterStock === 'in') result = result.filter(p => p.remaining > 5);
                else if (this.filterStock === 'low') result = result.filter(p => p.remaining > 0 && p.remaining <=
                    5);
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
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        this.requestOpen = false;
                        this.requestForm = {
                            product_id: '',
                            product_name: '',
                            quantity: 1,
                            note: ''
                        };
                        window.location.reload();
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
                console.log('Items:', this.newProductList);
                const validItems = this.newProductList.filter(item => item.product_id || item.name.trim());
                if (validItems.length === 0) return alert('Add at least one product');
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
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        this.requestNewProduct = false;
                        this.newProductList = [{
                            product_id: '',
                            name: '',
                            quantity: 1,
                            note: ''
                        }];
                        window.location.reload();
                    });
            },

            reportLoss(productId, productName, maxQty) {
                this.returnForm = {
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
                    alert('Cannot report more than available: ' + this.returnForm.maxQuantity);
                    return;
                }

                fetch('/cashier/stock-loss', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
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
        }
    }
</script>
