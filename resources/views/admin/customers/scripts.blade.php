<script>
    function customerPage() {
        return {
            open: false,
            editMode: false,
            viewOpen: false,

            customers: @json($customers),
            searchQuery: '',
            filterSegment: '',
            sortBy: 'recent',
            customerDetailOpen: false,
            customerProfile: {},
            customerOrders: [],
            filterSegment: '',
            searchQuery: '',
            sortBy: 'recent',

            orderDetailOpen: false,
            receiptOpen: false,
            receiptData: {},
            lastOrder: {},

            // refound 
            refundOpen: false,
            refundOrderId: null,
            refundOrderNumber: '',
            refundTotal: 0,
            refundReason: '',
            restockItems: true,

            currentPage: 1,
            perPage: 12,

            viewCustomerData: {},
            form: {
                id: null,
                name: '',
                email: '',
                phone: '',
                address: '',
                segment: 'regular',
                loyalty_points: 0,
            },

            refundOrder(id) {
                const order = this.orders.find(o => o.id === id);
                if (!order) return;
                this.refundOrderId = id;
                this.refundOrderNumber = order.order_number;
                this.refundTotal = parseFloat(order.total).toFixed(2);
                this.refundOpen = true;
            },

            processRefund() {
                fetch(`/admin/orders/${this.refundOrderId}/refund`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            reason: this.refundReason,
                            restock: this.restockItems,
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        this.refundOpen = false;
                        window.location.reload();
                    });
            },

            viewOrder(id) {
                fetch(`/admin/customers/${this.customerProfile.id}/order/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        const order = data.order;
                        this.receiptData = {
                            order_number: order.order_number,
                            items: order.items.map(i => ({
                                id: i.id,
                                name: i.name,
                                price: parseFloat(i.price) || 0,
                                qty: i.quantity,
                                base_unit: i.base_unit || '',
                            })),
                            subtotal: parseFloat(order.subtotal) || 0,
                            tax: parseFloat(order.tax) || 0,
                            total: parseFloat(order.total) || 0,
                            discount: parseFloat(order.discount) || 0,
                            vip_discount: parseFloat(order.vip_discount) || 0,
                            is_vip: order.is_vip || false,
                            status: order.status, // ← Add
                            refund_reason: order.refund_reason,
                            payment_method: order.payment?.method,
                            amount_received: parseFloat(order.payment?.amount_received) || parseFloat(order
                                .total) || 0,
                            change: parseFloat(order.payment?.change) || 0,
                            customer: order.customer ? {
                                name: order.customer.name
                            } : null,
                        };
                        this.lastOrder = {
                            order_number: order.order_number
                        };
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
                    });
            },

            emptyForm() {
                return {
                    id: null,
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                    segment: 'regular',
                    loyalty_points: 0,
                };
            },

            openAdd() {
                this.editMode = false;
                this.form = this.emptyForm();
                this.open = true;
            },

            openEdit(customer) {
                this.editMode = true;
                this.form = {
                    id: customer.id ?? null,
                    name: customer.name ?? '',
                    email: customer.email ?? '',
                    phone: customer.phone ?? '',
                    address: customer.address ?? '',
                    segment: customer.segment ?? 'regular',
                    loyalty_points: customer.loyalty_points ?? 0,
                };
                this.open = true;
            },

            get filteredCustomers() {
                let result = [...this.customers];

                // Filter by segment
                if (this.filterSegment) {
                    result = result.filter(c => c.segment === this.filterSegment);
                }

                // Search
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(c =>
                        c.name.toLowerCase().includes(q) ||
                        c.phone.includes(q) ||
                        c.email.toLowerCase().includes(q)
                    );
                }

                // Sort
                if (this.sortBy === 'spent') {
                    result.sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
                } else if (this.sortBy === 'orders') {
                    result.sort((a, b) => b.total_orders - a.total_orders);
                } else {
                    result.sort((a, b) => new Date(b.last_order_at || 0) - new Date(a.last_order_at || 0));
                }

                return result;
            },

            get totalPages() {
                return Math.ceil(this.filteredCustomers.length / this.perPage);
            },

            get paginatedCustomers() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredCustomers.slice(start, start + this.perPage);
            },

            get showingText() {
                const start = (this.currentPage - 1) * this.perPage + 1;
                const end = Math.min(this.currentPage * this.perPage, this.filteredCustomers.length);
                return `Showing ${start}-${end} of ${this.filteredCustomers.length} customers`;
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },

            goToPage(page) {
                if (typeof page === 'number') {
                    this.currentPage = page;
                    this.$nextTick(() => {
                        const el = this.$refs.tableBody;
                    });
                }
            },

            get pageNumbers() {
                const pages = [];
                for (let i = 1; i <= this.totalPages; i++) {
                    if (i === 1 || i === this.totalPages || (i >= this.currentPage - 2 && i <= this.currentPage +
                            2)) {
                        pages.push(i);
                    } else if (pages[pages.length - 1] !== '...') {
                        pages.push('...');
                    }
                }
                return pages;
            },

            viewCustomer(customer) {
                const segmentColors = {
                    vip: 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
                    regular: 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                    new: 'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400',
                    inactive: 'bg-gray-50 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400',
                };
                const segmentLabels = {
                    vip: 'VIP',
                    regular: 'Regular',
                    new: 'New',
                    inactive: 'Inactive',
                };
                const initials = customer.name.split(' ').map(n => n[0].toUpperCase()).slice(0, 2).join('');

                this.viewCustomerData = {
                    ...customer,
                    initials: initials,
                    segmentClass: segmentColors[customer.segment] || segmentColors.regular,
                    segmentLabel: segmentLabels[customer.segment] || 'Regular',
                    recent_orders: customer.recent_orders || [{
                            id: '001',
                            date: 'Nov 25, 2023',
                            amount: 156.50,
                            status: 'Completed'
                        },
                        {
                            id: '002',
                            date: 'Nov 18, 2023',
                            amount: 89.00,
                            status: 'Completed'
                        },
                    ]
                };
                this.viewOpen = true;
            },

            openEditFromView() {
                this.closeView();
                this.openEdit(this.viewCustomerData);
            },

            closePanel() {
                this.open = false;
            },

            closeView() {
                this.viewOpen = false;
            },

            openCustomerDetail(customerId) {
                fetch(`/admin/customers/${customerId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.customerProfile = data.customer;
                        this.customerOrders = data.orders;
                        this.customerDetailOpen = true;
                    });
            },
        }
    }
</script>
