<div class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">All Orders</h3>
        <div class="flex items-center gap-3 text-xs">
            <span class="text-gray-600 dark:text-zinc-400">{{ $orders->count() }} total</span>
            <span class="text-green-600">{{ $orders->where('status', 'completed')->count() }} completed</span>
            <span class="text-red-500">{{ $orders->where('status', 'refunded')->count() }} refunded</span>
        </div>
    </div>
    <div class="overflow-x-auto max-h-[600px] overflow-y-auto tab-container">
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white dark:bg-zinc-900">
                <tr
                    class="text-left text-xs text-gray-600 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="py-3 pl-4 font-medium">Order</th>
                    <th class="py-3 px-3 font-medium">Cashier</th>
                    <th class="py-3 font-medium">Customer</th>
                    <th class="py-3 px-3 font-medium text-center">Items</th>
                    <th class="py-3 px-3 font-medium text-center">Total</th>
                    <th class="py-3 px-3 font-medium">Payment</th>
                    <th class="py-3 px-3 font-medium pl-14 text-left">Date</th>
                    <th class="py-3 pr-4 font-medium text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pl-4 font-medium text-gray-800 dark:text-zinc-200 text-xs">
                            {{ $order->order_number }}
                        </td>
                        <td class="py-3 px-3 text-xs font-medium text-gray-600 dark:text-zinc-400">
                            {{ $order->cashier->name ?? '-' }}
                        </td>
                        <td class="py-3 text-xs font-medium text-gray-600 dark:text-zinc-400">
                            {{ $order->customer->name ?? 'Walk-in' }}
                        </td>
                        <td class="py-3 px-3 text-center text-xs text-gray-600 dark:text-zinc-400">
                            {{ $order->items->sum('quantity') }}
                        </td>
                        <td class="py-3 px-3 text-center text-xs font-semibold text-[#0F6E8C] dark:text-[#1898be]">
                            ${{ number_format($order->total, 2) }}
                        </td>
                        <td class="py-3 px-3">
                            <span
                                class="px-1.5 py-0.5 text-[11px] rounded-full font-medium capitalize
                            {{ $order->payment->method === 'cash'
                                ? 'bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-400 border dark:border-green-800/50'
                                : ($order->payment->method === 'card'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border dark:border-blue-800/50'
                                    : 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border dark:border-purple-800/50') }}">
                                {{ $order->payment->method ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-xs text-gray-500 dark:text-zinc-500">
                            {{ $order->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="py-3 pr-4 text-center">
                            <span
                                class="px-1.5 py-0.5 text-[11px] rounded-full font-medium
                            {{ $order->status === 'completed'
                                ? 'bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-400 border dark:border-green-800/50'
                                : ($order->status === 'partially_refunded'
                                    ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 border dark:border-yellow-800/50'
                                    : 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400 border dark:border-red-800/50') }}">
                                {{ ucfirst($order->status) }}
                            </span>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-400 dark:text-zinc-500 text-sm">No orders
                            found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
