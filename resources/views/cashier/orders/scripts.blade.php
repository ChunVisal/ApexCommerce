<script>
    function orderPage() {
        return {
            orderDetailOpen: false,
            receiptOpen: false,
            receiptData: {},
            lastOrder: {},

            searchQuery: '',
            dateFilter: 'all',
            paymentFilter: 'all',
            orders: @json($orders),

            currentPage: 1,
            perPage: 15,

            // refound 
            refundOpen: false,
            refundOrderId: null,
            refundOrderNumber: '',
            refundTotal: 0,
            refundReason: '',
            refundReasonSelect: '',
            refundOrderItems: [], // NEW
            refundSelection: {},
            restockItems: true,

            get filteredOrders() {
                let result = [...this.orders];

                // Search filter
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(o =>
                        (o.order_number || '').toLowerCase().includes(q) ||
                        (o.customer?.name || '').toLowerCase().includes(q)
                    );
                }

                // Payment filter
                if (this.paymentFilter !== 'all') {
                    result = result.filter(o => o.payment?.method === this.paymentFilter);
                }
                return result;
            },

            get totalPages() {
                return Math.ceil(this.filteredOrders.length / this.perPage);
            },

            get showingText() {
                const start = (this.currentPage - 1) * this.perPage + 1;
                const end = Math.min(this.currentPage * this.perPage, this.filteredOrders.length);
                return `Showing ${start}-${end} of ${this.filteredOrders.length} entries`;
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

            searchOrders() {
                fetch(
                        `/cashier/orders?search=${encodeURIComponent(this.searchQuery)}&payment=${this.paymentFilter}&ajax=1`
                    )
                    .then(res => res.json())
                    .then(data => {
                        this.orders = data.orders;
                    });
            },

            filterPayment(method) {
                this.paymentFilter = method;
                fetch(`/cashier/orders?payment=${method}&search=${encodeURIComponent(this.searchQuery)}&ajax=1`)
                    .then(res => res.json())
                    .then(data => {
                        this.orders = data.orders;
                    });
            },

            refundOrder(id) {
                const order = this.orders.find(o => o.id === id);
                if (!order) return;
                this.refundOrderId = id;
                this.refundOrderNumber = order.order_number;
                this.refundTotal = parseFloat(order.total).toFixed(2);
                this.refundOrderItems = order.items || [];
                this.refundSelection = {};
                this.refundOrderItems.forEach(item => {
                    this.refundSelection[item.id] = {
                        selected: false,
                        broken: false,
                        quantity: 0,
                    };
                });

                // sum only items NOT already refunded — this is what's actually still refundable
                const remainingTotal = this.refundOrderItems
                    .filter(item => !item.is_refunded)
                    .reduce((sum, item) => sum + parseFloat(item.total), 0);
                this.refundTotal = remainingTotal.toFixed(2);

                this.refundOpen = true;
            },

            toggleRefundItem(itemId, maxQty) {
                this.refundSelection[itemId].selected = !this.refundSelection[itemId].selected;
                if (this.refundSelection[itemId].selected) {
                    this.refundSelection[itemId].quantity = maxQty; // default to refunding ALL remaining units
                } else {
                    this.refundSelection[itemId].broken = false;
                    this.refundSelection[itemId].quantity = 0;
                }
            },

            toggleBroken(itemId) {
                this.refundSelection[itemId].broken = !this.refundSelection[itemId].broken;
            },

            processRefund() {
                const items = Object.entries(this.refundSelection)
                    .filter(([id, val]) => val.selected)
                    .map(([id, val]) => ({
                        order_item_id: parseInt(id),
                        quantity: val.quantity,
                        restock: !val.broken,
                    }));
                if (items.length === 0) {
                    this.$dispatch('toast', {
                        message: 'Please select at least one item to refund',
                        type: 'error'
                    });
                    return;
                }

                fetch(`/cashier/orders/${this.refundOrderId}/refund`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            reason: this.refundReasonSelect === 'Other' ?
                                this.refundReason :
                                this.refundReasonSelect,
                            items: items,
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
                                message: data.message || 'Refund failed',
                                type: 'error'
                            });
                            return;
                        }

                        this.$dispatch('toast', {
                            message: data.message,
                            type: 'success'
                        });

                        // Update refundOrderItems — what the slideover itself displays
                        items.forEach(refundItem => {
                            const item = this.refundOrderItems.find(i => i.id === refundItem.order_item_id);
                            if (item) {
                                item.refunded_quantity = (item.refunded_quantity || 0) + refundItem
                                    .quantity;
                                item.is_refunded = item.refunded_quantity >= item.quantity;
                                item.refund_type = refundItem.restock ? 'restock' : 'broken';
                            }
                        });

                        const order = this.orders.find(o => o.id === this.refundOrderId);
                        if (order) {
                            const allFullyRefunded = this.refundOrderItems.every(i => i.is_refunded);
                            order.total = data.total;
                            order.status = allFullyRefunded ? 'refunded' : 'partially_refunded';
                            order.refunded_at = data.refunded_at;
                        }

                        this.refundOpen = false;
                    })
                    .catch(err => {
                        this.$dispatch('toast', {
                            message: 'Network error: ' + err.message,
                            type: 'error'
                        });
                    });
            },

            viewOrder(id, v = false) {
                fetch(`/cashier/orders/${id}`)
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
                                refunded_quantity: i.refunded_quantity || 0,
                            })),
                            subtotal: parseFloat(order.subtotal) || 0,
                            tax: parseFloat(order.tax) || 0,
                            net: parseFloat(order.net) || 0,
                            total: parseFloat(order.total) || 0,
                            discount: parseFloat(order.discount) || 0,
                            vip_discount: parseFloat(order.vip_discount) || 0,
                            is_vip: order.is_vip || false,
                            status: order.status,
                            refund_reason: order.refund_reason,
                            payment_method: order.payment?.method,
                            amount_received: parseFloat(order.payment?.amount_received) || parseFloat(order
                                .total) || 0,
                            change: parseFloat(order.payment?.change) || 0,
                            customer: order.customer ? {
                                name: order.customer.name
                            } : null,
                        };
                        console.log(this.receiptData);
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
        };
    }
</script>
