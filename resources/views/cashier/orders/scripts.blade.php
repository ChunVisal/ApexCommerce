<script>
    function orderPage() {
        return {
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
            refundReasonSelect: '',
            refundOrderItems: [], // NEW
            refundSelection: {},
            restockItems: true,

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
                            reason: this.refundReason,
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

                        items.forEach(refundItem => {
                            const item = this.refundOrderItems.find(i => i.id === refundItem.order_item_id);
                            if (item) {
                                item.is_refunded = true;
                                item.refund_type = refundItem.restock ? 'restock' : 'broken';
                            }
                        });

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
