<script>
    document.addEventListener('DOMContentLoaded', function() {
        // searching filter 
        $(document).ready(function() {
            let searchTimer;

            $('#search').on('input', function() {
                clearTimeout(searchTimer);
                const query = $(this).val();
                $('#clearSearch').toggle(query.length > 0);

                searchTimer = setTimeout(function() {
                    $.get('{{ route('cashier.pos') }}', {
                        search: query,
                        ajax: 1
                    }, function(data) {
                        $('#productGridContainer').html(data);
                    });
                }, 400);
            });

            $('#clearSearch').on('click', function() {
                $('#search').val('').trigger('input');
            });
        });
    });

    function posPage() {
        return {


            selectedCategory: 'all',
            categoryMap: @json($categoryCounts),
            cartItems: [],
            checkoutOpen: false,
            paymentMethod: 'cash',
            amountReceived: 0,
            change: 0,

            // discount
            discountType: 'fixed',
            discountValue: 0,

            // In posPage() data:
            receiptData: {
                customer: null,
                items: [],
                subtotal: 0,
                tax: this.tax,
                is_vip: this.isVipCustomer,
                vip_discount: this.vipDiscount,
                discount: this.discount,
                total: 0,
                payment_method: 'cash',
                amount_received: 0,
                change: 0,
            },

            receiptOpen: false,
            lastOrder: {},
            submitting: false,

            // Customer
            customerOpen: false,
            customerSearch: '',
            customerResults: [],
            customerForm: {
                name: '',
                phone: '',
                email: ''
            },
            selectedCustomer: null,
            customerSaved: false,

            heldCartsList: JSON.parse(localStorage.getItem('heldCarts') || '[]'),

            get subtotal() {
                return this.cartItems.reduce((sum, i) => sum + (i.price * i.qty), 0);
            },

            get discount() {
                let total = 0;
                // Manual discount
                if (this.discountValue > 0) {
                    if (this.discountType === 'percent') {
                        total += this.subtotal * (this.discountValue / 100);
                    } else {
                        total += parseFloat(this.discountValue) || 0;
                    }
                }
                // VIP discount (only if no manual discount)
                if (total === 0 && this.isVipCustomer) {
                    total += this.subtotal * 0.05;
                }
                return total;
            },

            get discountedSubtotal() {
                return Math.max(0, this.subtotal - this.discount);
            },
            get tax() {
                return (this.subtotal - this.manualDiscount - this.vipDiscount) * 0.10;
            },

            get isVipCustomer() {
                const vip = this.selectedCustomer?.segment === 'vip';
                if (vip && this.discountValue > 0 && this.discountValue === parseFloat(this.vipDiscount.toFixed(
                        2))) {
                    // If manual discount equals VIP discount, it was auto-set. Reset it.
                    this.discountValue = 0;
                }
                return vip;
            },
            get vipDiscount() {
                if (!this.selectedCustomer || this.selectedCustomer.segment !== 'vip') return 0;
                return this.subtotal * 0.05;
            },
            // Don't auto-fill manual discount with VIP
            get manualDiscount() {
                if (!this.discountValue || this.discountValue <= 0) return 0;
                if (this.discountType === 'percent') {
                    return this.subtotal * (this.discountValue / 100);
                }
                return parseFloat(this.discountValue) || 0;
            },

            get totalDiscount() {
                return this.vipDiscount + this.manualDiscount;
            },
            get total() {
                return this.subtotal + this.tax - this.manualDiscount - this.vipDiscount;
            },

            init() {
                this.$watch('discountValue', () => {
                    if (this.amountReceived) {
                        this.amountReceived = this.total.toFixed(2);
                        this.calculateChange();
                    }
                });
                this.discountValue = 0;
                this.$watch('vipDiscount', () => {
                    if (this.amountReceived) {
                        this.amountReceived = this.total.toFixed(2);
                        this.calculateChange();
                    }
                });
                this.$watch('discountValue', (val) => {
                    console.log('discountValue changed to:', val, new Error().stack);
                });
            },

            hasProducts() {
                if (this.selectedCategory === 'all') return true;
                return (this.categoryMap[this.selectedCategory] || 0) > 0;
            },

            addToCart(product) {
                const existing = this.cartItems.find(i => i.id === product.id);
                const currentQty = existing ? existing.qty : 0;
                const maxStock = product.stock;

                if (currentQty >= maxStock) {
                    return;
                }

                if (existing) {
                    existing.qty++;
                } else {
                    this.cartItems.push({
                        ...product,
                        qty: 1
                    });
                }
            },

            showAllCustomers() {
                this.customerSearch = '';
                this.searchCustomers();
            },

            increaseQty(index) {
                const item = this.cartItems[index];
                if (item.qty >= item.stock) {
                    return;
                }
                this.cartItems[index].qty++;
            },

            decreaseQty(index) {
                if (this.cartItems[index].qty > 1) this.cartItems[index].qty--;
                else this.cartItems.splice(index, 1);
            },
            removeItem(index) {
                this.cartItems.splice(index, 1);
            },
            openCheckout() {
                if (this.cartItems.length === 0) return;
                this.customerForm = {
                    name: '',
                    phone: '',
                    email: ''
                };
                this.customerSearch = '';
                this.customerResults = [];

                this.checkoutOpen = true;
                this.amountReceived = '';
                this.change = 0;
                this.paymentMethod = 'cash';
            },

            calculateChange() {
                this.change = Math.max(0, this.amountReceived - this.total);
            },

            searchCustomers() {
                const query = this.customerSearch || '';
                fetch(`/cashier/customers/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => this.customerResults = data);
            },

            selectCustomer(cust) {
                this.selectedCustomer = cust;

                this.customerForm = {
                    name: cust.name,
                    phone: cust.phone,
                    email: cust.email || ''
                };
                this.customerResults = [];
                this.customerSearch = '';
            },

            openAddCustomer() {
                this.customerForm = {
                    name: '',
                    phone: '',
                    email: ''
                };
                this.selectedCustomer = null;
                this.customerOpen = true;
            },

            saveCustomer() {
                console.log('saveCustomer called');
                if (!this.customerForm.name || !this.customerForm.phone) return;

                this.selectedCustomer = {
                    name: this.customerForm.name,
                    phone: this.customerForm.phone,
                    email: this.customerForm.email || null,
                    segment: this.selectedCustomer?.segment || 'new',
                };
                this.customerSaved = true;
                this.customerOpen = false;
            },

            get requiresCustomer() {
                return this.total >= 700;
            },

            holdCart() {
                if (this.cartItems.length === 0) return;

                const cart = {
                    id: Date.now(),
                    items: [...this.cartItems],
                    customer: this.selectedCustomer ? {
                        name: this.selectedCustomer.name
                    } : null,
                    note: '',
                    createdAt: new Date().toLocaleString(),
                };

                this.heldCartsList.push(cart);
                localStorage.setItem('heldCarts', JSON.stringify(this.heldCartsList));
                this.cartItems = [];
                this.selectedCustomer = null;
            },

            resumeCart(cart) {
                if (this.cartItems.length > 0 && !confirm('Replace current cart?')) return;
                this.cartItems = cart.items;
                this.selectedCustomer = cart.customer;
                this.heldCartsList = this.heldCartsList.filter(c => c.id !== cart.id);
                localStorage.setItem('heldCarts', JSON.stringify(this.heldCartsList));
            },

            processPayment() {

                if (this.paymentMethod === 'cash') {
                    const received = Math.round((parseFloat(this.amountReceived) || 0) * 100) / 100;
                    const total = Math.round(this.total * 100) / 100;

                    if (!this.amountReceived || received < total) {
                        alert('Insufficient amount. Need: $' + total.toFixed(2) + ', Received: $' + received.toFixed(
                            2));
                        return;
                    }
                }
                if (this.requiresCustomer && !this.selectedCustomer) {
                    alert('Orders over $700 require customer information. Please add customer details.');
                    this.customerOpen = true;
                    return;
                }
                this.submitting = true;

                fetch('{{ route('cashier.checkout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            items: this.cartItems.map(i => ({
                                id: i.id,
                                qty: i.qty
                            })),
                            payment_method: this.paymentMethod,
                            total: this.total,
                            subtotal: this.subtotal,
                            tax: this.tax,
                            discount: this.manualDiscount,
                            discount_type: this.discountType,
                            discount_value: this.discountValue,
                            is_vip: this.isVipCustomer,
                            vip_discount: this.vipDiscount,
                            amount_received: this.paymentMethod === 'cash' ? parseFloat(this
                                .amountReceived) : this.total,
                            customer: this.selectedCustomer ? {
                                name: this.selectedCustomer.name,
                                phone: this.selectedCustomer.phone,
                                email: this.selectedCustomer.email,
                            } : null,
                        }),
                    })
                    .then(res => {
                        return res.json();
                    })
                    .then(data => {

                        this.submitting = false;
                        if (data.success) {
                            this.receiptData = {
                                order_number: data.order.order_number,
                                items: [...this.cartItems],
                                subtotal: this.subtotal,
                                tax: this.tax,
                                discount: this.manualDiscount,
                                total: data.order.total,
                                is_vip: this.isVipCustomer,
                                vip_discount: this.vipDiscount,
                                payment_method: this.paymentMethod,
                                amount_received: this.amountReceived,
                                change: data.order.change,
                                customer: this.selectedCustomer ? JSON.parse(JSON.stringify(this
                                    .selectedCustomer)) : null,
                            };

                            this.lastOrder = data.order;
                            this.checkoutOpen = false;
                            this.receiptOpen = true;
                            this.$nextTick(() => {
                                if (this.receiptData.order_number) {
                                    JsBarcode("#barcode", this.receiptData.order_number, {
                                        format: "CODE128",
                                        width: 1.5,
                                        height: 40,
                                        displayValue: false,
                                        margin: 0,
                                        background: "transparent",
                                        lineColor: "#000",
                                    });
                                }
                            });

                            // 2. THEN clear everything
                            this.cartItems = [];
                            this.amountReceived = '';
                            this.change = 0;
                            this.selectedCustomer = null;
                            this.customerSaved = false;
                            this.customerForm = {
                                name: '',
                                phone: '',
                                email: ''
                            };
                        }
                    })
                    .catch(err => {
                        this.submitting = false;
                        alert('Network error. Please try again.');
                        console.error(err);
                    });
            },

            // Add these to your posPage or checkout component
            appendAmount(num) {
                if (!this.amountReceived) this.amountReceived = '';
                this.amountReceived += num;
                this.calculateChange();
            },

            backspaceAmount() {
                if (this.amountReceived) {
                    this.amountReceived = this.amountReceived.toString().slice(0, -1);
                    this.calculateChange();
                }
            },
            calculateChange() {
                const received = parseFloat(this.amountReceived) || 0;
                this.change = Math.max(0, received - this.total);
            },

            // KHQR Timer
            timer: 300,
            timerInterval: null,

            startTimer() {
                this.timer = 300;
                clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    if (this.timer > 0) this.timer--;
                    else clearInterval(this.timerInterval);
                }, 1000);
            },

            formatTime(seconds) {
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return m + ':' + String(s).padStart(2, '0');
            },

            openTestReceipt() {
                this.receiptData = {
                    order_number: 'INV-00001',
                    items: [{
                            id: 1,
                            name: 'RTX 4070 Graphics Card',
                            price: 599,
                            qty: 1
                        },
                        {
                            id: 2,
                            name: 'Corsair 32GB RAM',
                            price: 89,
                            qty: 2
                        },
                        {
                            id: 3,
                            name: 'Samsung 1TB SSD',
                            price: 79,
                            qty: 1
                        },
                    ],
                    subtotal: 856.00,
                    tax: 85.60,
                    total: 941.60,
                    payment_method: 'cash',
                    amount_received: 1000.00,
                    change: 58.40,
                };
                this.receiptOpen = true;
            },
        };
    }
</script>
