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
            restockItems: true,

            refundOrder(id) {
                const order = this.orders.find(o => o.id === id);
                if (!order) return;
                this.refundOrderId = id;
                this.refundOrderNumber = order.order_number;
                this.refundTotal = parseFloat(order.total).toFixed(2);
                this.refundOpen = true;
            },

            processRefund() {
                fetch(`/cashier/orders/${this.refundOrderId}/refund`, {
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
