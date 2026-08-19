{{-- Segment Filters --}}
<div class="flex flex-wrap gap-2 mb-4">
    <button @click="filterSegment = ''"
        :class="filterSegment === '' ? 'bg-[#0F6E8C] text-white border-[#0F6E8C]' :
            'bg-white dark:bg-zinc-900 text-gray-600 dark:text-zinc-400 border-gray-300 dark:border-zinc-700'"
        class="px-3 py-1.5 text-xs font-medium rounded-full border transition">
        All Customers
        <span class="ml-1 opacity-70">({{ $customers->count() }})</span>
    </button>
    <button @click="filterSegment = 'vip'"
        :class="filterSegment === 'vip' ? 'bg-yellow-500 text-white border-yellow-500' :
            'bg-white dark:bg-zinc-900 text-gray-600 dark:text-zinc-400 border-gray-300 dark:border-zinc-700'"
        class="px-3 py-1.5 text-xs font-medium rounded-full border transition">
        VIP Members
        <span class="ml-1 opacity-70">({{ $customers->where('segment', 'vip')->count() }})</span>
    </button>
    <button @click="filterSegment = 'regular'"
        :class="filterSegment === 'regular' ? 'bg-blue-500 text-white border-blue-500' :
            'bg-white dark:bg-zinc-900 text-gray-600 dark:text-zinc-400 border-gray-300 dark:border-zinc-700'"
        class="px-3 py-1.5 text-xs font-medium rounded-full border transition">
        Regular Customers
        <span class="ml-1 opacity-70">({{ $customers->where('segment', 'regular')->count() }})</span>
    </button>
    <button @click="filterSegment = 'new'"
        :class="filterSegment === 'new' ? 'bg-green-500 text-white border-green-500' :
            'bg-white dark:bg-zinc-900 text-gray-600 dark:text-zinc-400 border-gray-300 dark:border-zinc-700'"
        class="px-3 py-1.5 text-xs font-medium rounded-full border transition">
        New Customers
        <span class="ml-1 opacity-70">({{ $customers->where('segment', 'new')->count() }})</span>
    </button>
</div>

{{-- Search & Sort --}}
<div class="flex flex-wrap items-center gap-3 mb-4">
    <x-search-input placeholder="Search by name, email or phone..." />
    
    <div class="relative">
        <select x-model="sortBy" @change="filterCustomers()"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200">
            <option value="recent">Most Recent</option>
            <option value="spent">Highest Spent</option>
            <option value="orders">Most Orders</option>
        </select>
        <i class="fa-solid fa-chevron-down text-[10px] absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </div>
</div>

{{-- Customers Table --}}
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="pb-2 pr-4 font-medium">Customer</th>
                    <th class="pb-2 px-4 font-medium">Segment</th>
                    <th class="pb-2 px-4 font-medium text-center">Orders</th>
                    <th class="pb-2 px-4 font-medium text-right">Total Spent</th>
                    <th class="pb-2 pl-10 font-medium">Last Order</th>
                    <th class="pb-2 pl-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                <template x-for="customer in filteredCustomers.length > 0 ? filteredCustomers : []"
                    :key="customer.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3">

                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-xs font-semibold text-white shrink-0"
                                    style="background: linear-gradient(135deg, #0F6E8C, #1a8aa8);"
                                    x-text="customer.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase()">
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-zinc-200" x-text="customer.name">
                                    </p>
                                    <p class="text-xs truncate text-gray-400 dark:text-zinc-500"
                                        x-text="customer.email"></p>
                                    <p class="text-xs truncate text-gray-400 dark:text-zinc-500"
                                        x-text="customer.phone"></p>
                                </div>

                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full"
                                :class="{
                                    'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400': customer
                                        .segment === 'vip',
                                    'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400': customer
                                        .segment === 'regular',
                                    'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400': customer
                                        .segment === 'new'
                                }"
                                x-text="customer.segment.toUpperCase()">>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center text-gray-600 dark:text-zinc-400"
                            x-text="customer.total_orders"></td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-800 dark:text-zinc-200"
                            x-text="'$' + parseFloat(customer.total_spent, 2 || 0).toFixed(2)"></td>
                        <td class="py-3 pl-10 text-gray-500 dark:text-zinc-400 text-xs"
                            x-text="customer.last_order_at ? new Date(customer.last_order_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-'">
                        </td>

                        <td class="py-3 pl-4">
                            <div class="flex items-center justify-end">
                                {{-- yellow --}}
                                <button @click="openCustomerDetail(customer.id)"
                                    class="text-yellow-400 hover:text-yellow-500" title="View Orders">
                                    <i class="fa-solid fa-receipt text-[18px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredCustomers.length === 0">
                    <td colspan="6" class="text-center py-16">
                        <div class="flex flex-col items-center justify-center">
                            <div
                                class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-users text-2xl text-gray-400 dark:text-zinc-500"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No customers
                                found</h3>
                            <p class="text-xs text-gray-400 dark:text-zinc-500">No customers match your filters</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
