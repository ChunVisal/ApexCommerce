{{-- resources/views/components/customers/scripts.blade.php --}}
<script>
    function customerPage() {
        return {
            basePath: '{{ request()->is("admin/*") ? "admin" : "cashier" }}',
            
            customers: @json($customers),
            searchQuery: '',
            filterSegment: '',
            sortBy: 'recent',
            open: false,
            customerProfile: {},
            customerOrders: [],

            receiptOpen: false,
            receiptData: {},
            lastOrder: {},

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
                fetch(`/${this.basePath}/orders/${this.refundOrderId}/refund`, {
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
                const url = this.basePath === 'admin'
                    ? `/admin/customers/${this.customerProfile.id}/order/${id}`
                    : `/cashier/orders/${id}`;

                fetch(url)
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
                                is_refunded: i.is_refunded || false,
                            })),
                            subtotal: parseFloat(order.subtotal) || 0,
                            net: parseFloat(order.net) || 0,
                            tax: parseFloat(order.tax) || 0,
                            total: parseFloat(order.total) || 0,
                            discount: parseFloat(order.discount) || 0,
                            vip_discount: parseFloat(order.vip_discount) || 0,
                            is_vip: order.is_vip || false,
                            status: order.status,
                            refund_reason: order.refund_reason,
                            payment_method: order.payment?.method,
                            amount_received: parseFloat(order.payment?.amount_received) || parseFloat(order.total) || 0,
                            change: parseFloat(order.payment?.change) || 0,
                            customer: order.customer ? { name: order.customer.name } : null,
                        };
                        this.lastOrder = { order_number: order.order_number };
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
                    id: null, name: '', email: '', phone: '', address: '',
                    segment: 'regular', loyalty_points: 0,
                };
            },

            get filteredCustomers() {
                let result = [...this.customers];

                if (this.filterSegment) {
                    result = result.filter(c => c.segment === this.filterSegment);
                }

                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(c =>
                        c.name.toLowerCase().includes(q) ||
                        c.phone.includes(q) ||
                        c.email.toLowerCase().includes(q)
                    );
                }

                if (this.sortBy === 'spent') {
                    result.sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
                } else if (this.sortBy === 'orders') {
                    result.sort((a, b) => b.total_orders - a.total_orders);
                } else if (this.sortBy === 'recent') {
                    result.sort((a, b) => new Date(b.last_order_at || 0) - new Date(a.last_order_at || 0));
                } else if (this.sortBy === 'code') {
                    result.sort((a, b) => (a.code || '').localeCompare(b.code || ''));
                }

                return result;
            },

            openCustomerDetail(customerId) {
                fetch(`/${this.basePath}/customers/${customerId}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    this.customerProfile = data.customer;
                    this.customerOrders = data.orders;
                    this.open = true;
                });
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
                return `Showing ${start}-${end} of ${this.filteredCustomers.length} entries`;
            },
            get pageNumbers() {
                const pages = [];
                for (let i = 1; i <= this.totalPages; i++) {
                    if (i === 1 || i === this.totalPages || (i >= this.currentPage - 2 && i <= this.currentPage +
                            2)) pages.push(i);
                    else if (pages[pages.length - 1] !== '...') pages.push('...');
                }
                return pages;
            },
            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },
            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },
            goToPage(page) {
                if (typeof page === 'number') this.currentPage = page;
                this.$nextTick(() => {
                    const el = this.$refs.tableBody;
                    if (el) el.scrollTop = 0;
                });
            },
        }
    }
</script>