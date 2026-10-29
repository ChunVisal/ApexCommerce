<div class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">All Orders</h3>
        <div class="flex items-center gap-3 text-xs">
            <span class="text-gray-600 dark:text-zinc-400">{{ $orders->count() }} total</span>
            <span class="text-green-600">{{ $orders->where('status', 'completed')->count() }} completed</span>
            <span class="text-red-500">{{ $orders->where('status', 'refunded')->count() }} refunded</span>
            <span
                class="text-yellow-600 dark:text-yellow-500">{{ $orders->where('status', 'partially_refunded')->count() }}
                Partially
                refunded</span>
        </div>
    </div>
    <div class="p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60">

        <x-table headers="headers.orders" rows="orders" as="order">
            <td class="py-3 text-left font-medium text-gray-800 dark:text-zinc-200 text-xs" x-text="order.order_number"></td>
            <td class="text-center text-xs font-medium text-gray-600 dark:text-zinc-400"
                x-text="order.cashier?.name ?? '-'"></td>
            <td class="text-center text-xs font-medium text-gray-600 dark:text-zinc-400"
                x-text="order.customer?.name ?? 'Walk-in'"></td>
            <td class="text-center  text-xs text-gray-600 dark:text-zinc-400" x-text="order.items_qty ?? 0">
            </td>
            <td class="text-center text-xs font-semibold text-[#0F6E8C] dark:text-[#1898be]"
                x-text="'$' + Number(order.total).toFixed(2)"></td>
            <td class="text-center">
                <span class="px-1.5 py-0.5 text-[11px] rounded-full font-medium capitalize"
                    :class="{
                        'bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-400 border dark:border-green-800/50': order
                            .payment?.method === 'cash',
                        'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border dark:border-blue-800/50': order
                            .payment?.method === 'card',
                        'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border dark:border-purple-800/50': order
                            .payment?.method !== 'cash' && order.payment?.method !== 'card'
                    }"
                    x-text="order.payment?.method ?? '-'"></span>
            </td>
            <td class="text-center text-xs text-gray-500 dark:text-zinc-500"
                x-text="new Date(order.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })">
            </td>
            <td class="text-right">
                <span class="px-1.5 py-0.5 text-[11px] rounded-full font-medium capitalize"
                    :class="{
                        'bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-400 border dark:border-green-800/50': order
                            .status === 'completed',
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 border dark:border-yellow-800/50': order
                            .status === 'partially_refunded',
                        'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400 border dark:border-red-800/50': order
                            .status !== 'completed' && order.status !== 'partially_refunded'
                    }"
                    x-text="order.status.replace('_', ' ')"></span>
            </td>
        </x-table>

        <x-empty-state icon="heroicon-o-shopping-bag" x-show="orders.length === 0" title="No orders found"
            description="No orders in the selected date range." />
    </div>
</div>
