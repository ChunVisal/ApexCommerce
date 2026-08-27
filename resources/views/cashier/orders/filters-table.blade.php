{{-- Search + Date Filter --}}
<div>
    <div class="flex items-center gap-3 mb-4">
        <x-search-input placeholder="Search order number or customer name..." />

        {{-- Date Filter --}}
        <div class="relative" x-data="{ open: false, selected: '{{ $selectedFilter }}' }">
            <button @click="open = !open"
                class="flex items-center gap-2 px-3 py-1.5 text-xs border border-gray-300 dark:border-zinc-800 rounded-md bg-white dark:bg-zinc-900  text-gray-700 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition-colors whitespace-nowrap">
                <i class="fa-regular fa-calendar text-[14px] text-gray-600 dark:text-zinc-400"></i>
                <span x-text="selected"></span>
                <i class="fa-solid fa-chevron-down text-gray-400 dark:text-zinc-500 text-[10px]"></i>
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak
                class="absolute right-0 mt-1 w-40 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-md shadow-lg dark:shadow-zinc-950/50 z-20 py-1">
                <a href="{{ route('cashier.orders', ['filter' => 'today']) }}"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Today
                </a>
                <a href="{{ route('cashier.orders', ['filter' => 'yesterday']) }}"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Yesterday</a>
                <a href="{{ route('cashier.orders', ['filter' => 'last_7_days']) }}"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Last
                    7 days</a>
                <a href="{{ route('cashier.orders', ['filter' => 'last_30_days']) }}"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Last
                    30 Days</a>
                <a href="{{ route('cashier.orders', ['filter' => 'all_time']) }}"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">All
                    Time</a>
            </div>
        </div>

        {{-- Payment Filter --}}
        <div class="relative" x-data="{ open: false, selected: 'All Payments' }">
            <button @click="open = !open"
                class="flex items-center gap-2 px-3 py-1.5 text-xs border border-gray-300 dark:border-zinc-800 rounded-md bg-white dark:bg-zinc-900 text-gray-700 dark:text-zinc-200 whitespace-nowrap hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition">
                <i class="fa-solid fa-credit-card text-[#1A1F7C] dark:text-[#4a9eb8]"></i>
                <span x-text="selected"></span>
                <i class="fa-solid fa-chevron-down text-gray-400 dark:text-zinc-400 text-[10px]"></i>
            </button>

            <div x-show="open"
                class="absolute right-0 mt-1 w-36 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-md shadow-lg z-20 py-1">
                <button @click="filterPayment('all')"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-700 transition">All
                    Payments</button>
                <button @click="filterPayment('cash')"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-700 transition">Cash</button>
                <button @click="filterPayment('card')"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-700 transition">Card</button>
                <button @click="filterPayment('khqr')"
                    class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-700 transition">KHQR</button>
            </div>
        </div>
    </div>

    {{-- Orders Component Box Container --}}
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
        {{-- Unified Scroll Contained Table Boundary Layout matching Customer Grid --}}
        <div class="tab-container overflow-auto max-h-[600px]" x-ref="tableBody">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-white dark:bg-zinc-900 z-10">
                    <tr
                        class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                        <th class="pb-2 pr-4 pl-4 font-medium">Order</th>
                        <th class="pb-2 px-4 font-medium">Customer</th>
                        <th class="pb-2 px-4 font-medium text-center">Items</th>
                        <th class="pb-2 px-4 font-medium">Total</th>
                        <th class="pb-2 px-4 font-medium">Payment</th>
                        <th class="pb-2 pl-10 font-medium text-left">Date</th>
                        <th class="pb-2 px-4 font-medium text-center">Refunded At</th>
                        <th class="pb-2 pr-4 pl-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                    {{-- Alpine Reactive Loop Layout --}}
                    <template x-for="order in filteredOrders" :key="order.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                            <td class="py-3 pl-4 pr-2 font-medium text-gray-800 dark:text-zinc-200"
                                x-text="order.order_number"></td>

                            <td class="py-3 px-4">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-zinc-200 truncate"
                                        x-text="order.customer?.name || 'Walk-in'"></p>
                                    <p class="text-xs text-gray-400 truncate" x-text="order.customer?.phone || ''"></p>
                                </div>
                            </td>

                            <td class="py-3 px-4 text-center font-medium text-gray-700 dark:text-zinc-300"
                                x-text="order.items.reduce((sum, i) => sum + (i.quantity || 0), 0)"></td>

                            <td class="font-medium text-gray-800 dark:text-zinc-300"
                                x-text="'$' + (parseFloat(order.total) || 0).toFixed(2)"></td>

                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full uppercase"
                                    :class="{
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': order
                                            .payment?.method === 'cash',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': order
                                            .payment?.method === 'card',
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': order
                                            .payment?.method === 'khqr'
                                    }"
                                    x-text="order.payment?.method || 'CASH'"></span>
                            </td>

                            <td class="text-gray-800 dark:text-zinc-300 text-left"
                                x-text="order.created_at ? new Date(order.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : '-'">
                            </td>

                            <td class="text-gray-800 dark:text-zinc-300 text-center"
                                x-text="order.refunded_at ? new Date(order.refunded_at).toLocaleDateString('en-US', {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : 'Not Refunded'">
                            </td>

                            <td class="py-3 pr-4 pl-2 text-right">
                                <button @click="viewOrder(order.id)" class="text-yellow-400 hover:text-yellow-500">
                                    <i class="fa-solid fa-receipt text-lg"></i>
                                </button>
                                <button x-show="order.status === 'completed'" @click="refundOrder(order.id)"
                                    class="text-xs font-medium text-red-500 hover:text-red-600 ml-2">
                                    Refund
                                </button>

                                <button x-show="order.status === 'partially_refunded'" @click="refundOrder(order.id)"
                                    class="text-xs font-medium text-amber-600 dark:text-amber-500 hover:text-orange-700 ml-2">
                                    Partially_refunded
                                </button>

                                <span x-show="order.status === 'refunded'"
                                    class="text-xs ml-1 font-medium text-green-600">
                                    Refunded
                                </span>
                            </td>
                        </tr>
                    </template>
                    {{-- Not Found - shows for any filter --}}
                    <tr x-show="filteredOrders.length === 0">
                        <td colspan="7" class="text-center py-16">
                            <div class="flex flex-col items-center justify-center">
                                <div
                                    class="w-14 h-14 mb-3 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-receipt text-xl text-gray-400"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300">No orders found</h3>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Alpine Pagination --}}
        <x-pagination />
    </div>
</div>
