<script>
    function productUomPage() {
        return {
            // Product list state
            products: @json($products),
            categories: @json($categories),

            // Slide-over visibility + mode
            uomFormOpen: false,
            editMode: false,
            submitting: false,

            // The product being created/edited
            uomFormProduct: null,

            // Category-based product picker (for NEW product creation)
            selectedProductName: '',
            categoryProducts: [],

            // Main form fields (category, name, image) — used only when creating new
            form: {
                category_code: '',
                name: '',
                image_url: '',
                image_preview: '',
                image_file: null,
                base_unit_name: '',
                base_unit_code: '',
                price: 0,
                status: 'active',
            },

            // Additional unit rows
            uomFormList: [{
                uom_id: '',
                name: '',
                code: '',
                quantity_per_unit: 1,
                price: 0,
                is_default: true
            }],

            get uomProducts() {
                return this.products.filter(p => p.uoms && p.uoms.length > 0);
            },
            // Open panel to CREATE a new product OR EDIT an existing product's UOMs
            openUomForm(product) {
                this.selectedProductName = '';
                this.categoryProducts = [];

                if (!product) {
                    // Creating a new UOM product
                    this.editMode = false;
                    this.uomFormProduct = null;

                    this.form = {
                        category_code: '',
                        name: '',
                        image_url: '',
                        image_preview: '',
                        image_file: null,
                        base_unit_name: '',
                        base_unit_code: '',
                        price: 0,
                        status: 'active',
                    };


                    this.uomFormList = [];
                } else {
                    // Editing an existing product's UOMs
                    this.editMode = true;
                    this.uomFormProduct = product;

                    this.form = {
                        category_code: product.category_code || '',
                        name: product.name || '',
                        image_url: product.image_url || '',
                        image_preview: '',
                        image_file: null,
                        base_unit_name: product.base_unit_name || '',
                        base_unit_code: product.base_unit_code || '',
                        price: product.price || 0,
                        status: product.status || 'active',
                    };

                    this.uomFormList = (product.uoms && product.uoms.length > 0) ?
                        JSON.parse(JSON.stringify(product.uoms)) : [];
                }

                this.uomFormOpen = true;
            },

            closeUomPanel() {
                this.uomFormOpen = false;
            },

            // Open panel to CREATE a brand new UOM product
            openAddUom() {
                this.editMode = false;
                this.uomFormProduct = null;
                this.selectedProductName = '';
                this.categoryProducts = [];

                this.form = {
                    category_code: '',
                    name: '',
                    image_url: '',
                    image_preview: '',
                    image_file: null,
                    base_unit_name: '',
                    base_unit_code: '',
                    price: 0,
                    status: 'active',
                };

                this.uomFormList = [{
                    uom_id: '',
                    name: '',
                    code: '',
                    quantity_per_unit: 1,
                    price: 0,
                    is_default: true
                }];

                this.uomFormOpen = true;
            },

            // Fetch products belonging to the selected category (for the "select existing name" dropdown)
            loadProducts() {
                if (!this.form.category_code) return;
                fetch(`/admin/products/by-category?category_code=${this.form.category_code}`)
                    .then(res => res.json())
                    .then(data => {
                        this.categoryProducts = data.products || data;
                    });
            },

            autoFillDetails() {
                const match = this.categoryProducts.find(p => p.name === this.selectedProductName);
                if (match) {
                    this.form.image_url = match.image_url || '';
                }
            },

            handleImageFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.form.image_file = file;
                this.form.image_url = '';

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.image_preview = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            // Recalculate all additional unit prices when base price changes
            updateUomPrices() {
                this.uomFormList.forEach(uom => {
                    if (uom.quantity_per_unit && this.form.price) {
                        uom.price = (parseFloat(this.form.price) * parseFloat(uom.quantity_per_unit)).toFixed(
                            2);
                    }
                });
            },

            submitUomForm() {
                if (!this.form.category_code || !this.form.name) {
                    alert('Category and product name are required');
                    return;
                }

                this.submitting = true;

                const payload = new FormData();
                payload.append('category_code', this.form.category_code);
                payload.append('name', this.form.name);
                payload.append('base_unit_name', this.form.base_unit_name);
                payload.append('base_unit_code', this.form.base_unit_code);
                payload.append('price', this.form.price);
                payload.append('status', this.form.status);
                payload.append('has_uom', 1);
                payload.append('uoms', JSON.stringify(this.uomFormList));

                if (this.form.image_file) {
                    payload.append('image', this.form.image_file);
                } else if (this.form.image_url) {
                    payload.append('image_url', this.form.image_url);
                }

                const url = this.editMode ?
                    `/admin/products/${this.uomFormProduct.id}/uoms` :
                    `/admin/products/uoms`;

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-HTTP-Method-Override': this.editMode ? 'PUT' : 'POST',
                        },
                        body: payload
                    })
                    .then(res => res.json())
                    .then(() => {
                        this.uomFormOpen = false;
                        window.location.reload();
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Something went wrong saving the UOM product.');
                    })
                    .finally(() => {
                        this.submitting = false;
                    });
            },
        }
    }
</script>
