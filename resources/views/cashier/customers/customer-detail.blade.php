{{-- Customer Detail Slideover --}}
<div x-show="customerDetailOpen" x-cloak class="fixed inset-0 z-50" style="display: none;">
    <div @click="customerDetailOpen = false" class="absolute inset-0 bg-black/50"></div>

    {{-- Panel --}}
    <div x-show="customerDetailOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="tab-container absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-[#0F6E8C] flex items-center justify-center text-white text-lg font-bold"
                    x-text="customerProfile.name?.charAt(0)?.toUpperCase()"></div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-zinc-100" x-text="customerProfile.name">
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-zinc-x300"
                        x-text="customerProfile.created_at ? 'Joined: ' + new Date(customerProfile.created_at).toLocaleDateString() : ''">
                    </p>
                    <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                        :class="customerProfile.segment === 'vip' ?
                            'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' : customerProfile
                            .segment === 'regular' ?
                            'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' :
                            'bg-green-300/30 text-green-700 dark:bg-green-900/30 dark:text-green-400'"
                        x-text="customerProfile.segment?.toUpperCase()"></span>
                </div>
            </div>
            <button @click="customerDetailOpen = false"
                class="text-gray-500 dark:text-zinc-300 hover:text-gray-600">✕</button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">

            {{-- Contact Info --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 dark:text-zinc-300 uppercase tracking-wider mb-2">Contact
                </h4>
                <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-zinc-300">Phone</span>
                        <span class="font-medium text-gray-800 dark:text-zinc-200"
                            x-text="customerProfile.phone"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-zinc-300">Email</span>
                        <span class="font-medium text-gray-800 dark:text-zinc-200"
                            x-text="customerProfile.email || '-'"></span>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 dark:text-zinc-300 uppercase tracking-wider mb-2">Summary
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-[#0F6E8C]/5 dark:bg-[#0F6E8C]/30 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-[#0F6E8C]" x-text="customerProfile.total_orders"></p>
                        <p class="text-[11px] text-gray-500 dark:text-zinc-300">Total Orders</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-green-600">$<span
                                x-text="parseFloat(customerProfile.total_spent || 0).toFixed(0)"></span></p>
                        <p class="text-[11px] text-gray-500 dark:text-zinc-300">Total Spent</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-950/20 rounded-lg p-3 text-center">
                        <p class="text-xl font-bold text-purple-600">$<span
                                x-text="customerProfile.avg_order?.toFixed(0) || '0'"></span></p>
                        <p class="text-[11px] text-gray-500 dark:text-zinc-300">Avg Order</p>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/40 rounded-lg p-3 text-center">
                        <p class="text-sm font-bold text-amber-600"
                            x-text="customerProfile.last_order_at ? new Date(customerProfile.last_order_at).toLocaleDateString() : 'Never'">
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-zinc-300">Last Order</p>
                    </div>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 dark:text-zinc-300 uppercase tracking-wider mb-2">Recent
                    Orders</h4>
                <div class="space-y-2">
                    <template x-for="order in customerOrders" :key="order.id">
                        <div @click="viewOrder(order.id)"
                            class="relative bg-gray-50 dark:bg-zinc-800 rounded-lg p-3 overflow-hidden">

                            <!-- Top Right Ribbon for refunded orders -->
                            <template x-if="order.status === 'refunded'">
                                <div class="absolute top-0 right-0 w-[90px] pointer-events-none select-none z-10">
                                    <div
                                        class="bg-red-100 text-red-700 text-[11px] font-semibold h-5 w-full flex items-center justify-center shadow rounded-bl-md">
                                        Refunded
                                    </div>
                                </div>
                            </template>

                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-semibold text-gray-800 dark:text-zinc-200"
                                    x-text="order.order_number"></span>
                            </div>
                            <div class="flex justify-between text-[11px] text-gray-500 dark:text-zinc-300 mb-1">
                                <span
                                    x-text="new Date(order.created_at).toLocaleDateString() + ' ' + new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })"></span>
                                <span class="capitalize" x-text="order.payment?.method || '-'"></span>

                            </div>
                            {{-- Order Items --}}
                            <div class="mt-1 pt-1 border-t border-gray-200 dark:border-zinc-700">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex justify-between text-[11px] text-gray-500 dark:text-zinc-300">
                                        <span x-text="item.quantity + 'x ' + item.name"></span>
                                        <span>$<span x-text="parseFloat(item.total).toFixed(2)"></span></span>
                                    </div>
                                </template>
                                <!-- Total row -->
                                <div
                                    class="flex justify-between text-[11px] font-semibold text-gray-700 dark:text-zinc-100 mt-2 border-t border-dashed border-gray-300 dark:border-zinc-700 pt-1">
                                    <span>Total</span>
                                    <span>
                                        $<span
                                            x-text="order.items.reduce((sum, item) => sum + parseFloat(item.total), 0).toFixed(2)">
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>


                    <div x-show="customerOrders.length === 0"
                        class="text-center py-4 text-xs text-gray-500 dark:text-zinc-300">
                        No orders yet
                    </div>
                </div>
            </div>


            {{-- Notes --}}
            <div x-show="customerProfile.notes">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-zinc-300 uppercase tracking-wider mb-2">Notes
                </h4>
                <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-3">
                    <p class="text-sm text-gray-600 dark:text-zinc-400" x-text="customerProfile.notes"></p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-gray-200 dark:border-zinc-800">
            <button @click="customerDetailOpen = false"
                class="w-full py-2 text-xs font-semibold text-gray-600 border border-gray-300 dark:border-zinc-600 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                Close
            </button>
        </div>
    </div>
</div>
