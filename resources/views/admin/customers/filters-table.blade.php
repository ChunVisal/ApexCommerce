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
    
    <x-filter-select model="sortBy">
        <option value="recent">Most Recent</option>
        <option value="spent">Highest Spent</option>
        <option value="orders">Most Orders</option>
        <option value="code">Code Range</option>
    </x-filter-select>
</div>

{{-- table customer --}}
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="tab-container overflow-auto max-h-[600px]" x-ref="tableBody">
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white dark:bg-zinc-900">
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="pb-2 font-medium">Customer</th>
                    <th class="pb-2 pr-2 font-medium">No.</th>
                    <th class="pb-2 px-5 font-medium">Phone Number</th>
                    <th class="pb-2 px-2 font-medium">Segment</th>
                    <th class="pb-2 px-4 font-medium text-center">Orders</th>
                    <th class="pb-2 px-4 font-medium">Total Spent</th>
                    <th class="pb-2 px-4 font-medium">Last Order</th>
                    <th class="pb-2 pl-4 font-medium text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                <template x-for="customer in paginatedCustomers" :key="customer.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                                    :style="'background-color: ' + ['#0F6E8C', '#1a8aa8', '#2563EB', '#7C3AED', '#059669',
                                        '#D97706'
                                    ][Math.floor(Math.random() * 6)]">
                                    <span
                                        x-text="customer.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2)"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-zinc-200 truncate"
                                        x-text="customer.name"></p>
                                    <p class="text-xs text-gray-400 truncate" x-text="customer.email"></p>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 pr-4 text-md font-mono text-gray-700 dark:text-zinc-300"
                            x-text="customer.code ?? '-'"></td>
                        <td class="py-3 px-5 text-xs text-gray-500 dark:text-zinc-400 truncate" x-text="customer.phone">
                        </td>

                        <td class="py-3 px-4 text-left whitespace-nowrap">
                            <template x-if="customer.total_orders >= 6 || customer.total_spent >= 5000">
                                <span
                                    class="px-2 py-0.5 text-[12px] font-semibold rounded-full bg-yellow-50 dark:bg-yellow-950/40 text-yellow-700 dark:text-yellow-300">
                                    VIP
                                </span>
                            </template>
                            <template
                                x-if="(customer.total_orders >= 3 || customer.total_spent >= 2000) && (customer.total_orders < 6 && customer.total_spent < 5000)">
                                <span
                                    class="px-2 py-0.5 text-[12px] font-semibold rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300">
                                    Regular
                                </span>
                            </template>
                            <template x-if="customer.total_orders < 3 && customer.total_spent < 2000">
                                <span
                                    class="px-2 py-0.5 text-[12px] font-semibold rounded-full bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300">
                                    New
                                </span>
                            </template>
                        </td>

                        <td class="py-3 px-2 text-center font-medium text-gray-700 dark:text-zinc-300"
                            x-text="customer.total_orders"></td>
                        <td class="py-3 px-4 font-semibold text-gray-700 dark:text-zinc-300">$<span
                                x-text="parseFloat(customer.total_spent || 0).toFixed(2)"></span></td>
                        <td class="py-3 px-2 text-xs text-gray-500 dark:text-zinc-400"
                            x-text="customer.last_order_at ? new Date(customer.last_order_at).toLocaleDateString('en-US', {hour: 'numeric', minute: 'numeric', month:'short', day:'numeric', year:'numeric'}) : '-'">
                        </td>
                        <td class="py-3 pr-4 pl-2 text-center">
                            <button @click="openCustomerDetail(customer.id)"
                                class="text-yellow-400 hover:text-yellow-500">
                                <i class="fa-solid fa-receipt text-lg"></i>
                            </button>
                        </td>
                    </tr>
                </template>
                {{-- Empty state for filter/searching --}}
                <tr x-show="customers.length > 0 && filteredCustomers.length === 0">
                    <td colspan="6" class="text-center py-16">
                        <div class="flex flex-col items-center justify-center">
                            <div
                                class="w-14 h-14 mb-3 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-users text-xl text-gray-400"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No results Customer
                                found</h3>
                            <p class="text-xs text-gray-400 dark:text-zinc-500">Get started by adding your first
                                customer.</p>
                        </div>
                    </td>
                </tr>

                {{-- Empty State if no customers exist yet --}}
                <tr x-show="customers.length === 0">
                    <td colspan="6" class="text-center py-16">
                        <div class="flex flex-col items-center justify-center">
                            <div
                                class="w-14 h-14 mb-3 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-users text-xl text-gray-400"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No customers exist
                                yet</h3>
                            <p class="text-xs text-gray-400 dark:text-zinc-500">Get started by adding your first
                                customer.</p>
                            <button @click="openAdd()"
                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition">
                                <i class="fa-solid fa-plus text-[10px]"></i> Add Your First Customer
                            </button>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-200 dark:border-zinc-800">
        <p class="text-xs text-gray-500 dark:text-zinc-400" x-text="showingText"></p>
        <div class="flex items-center gap-1">
            <button @click="prevPage()" :disabled="currentPage === 1" type="button"
                class="px-3 py-1 text-xs border border-gray-300 dark:border-zinc-700 rounded-md 
                text-gray-600 dark:text-zinc-300 
                bg-white dark:bg-zinc-900 
                transition
                hover:bg-[#0F6E8C] hover:text-white hover:border-[#0F6E8C]
                dark:hover:bg-[#173f4b] dark:hover:text-yellow-200 dark:hover:border-[#0F6E8C]
                disabled:opacity-60 disabled:cursor-not-allowed">
                Previous
            </button>
            <template x-for="page in pageNumbers" :key="page">
                <div>
                    <button x-show="page !== '...'" @click="goToPage(page)" type="button"
                        :class="[
                            'px-3 py-1 text-xs rounded-md border transition focus:outline-none',
                            currentPage === page ?
                            'bg-[#0F6E8C] text-white border-[#0F6E8C] dark:bg-[#0F6E8C] dark:border-[#0F6E8C] dark:text-yellow-200' :
                            'border-gray-300 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-[#f3fbfd] hover:text-[#0F6E8C] hover:border-[#0F6E8C] dark:hover:bg-[#173f4b] dark:hover:text-yellow-200 dark:hover:border-[#0F6E8C]'
                        ]">
                        <span x-text="page"></span>
                    </button>
                    <span x-show="page === '...'" class="px-2 text-gray-400 dark:text-zinc-600 select-none">...</span>
                </div>
            </template>
            <button @click="nextPage()" type="button" :disabled="currentPage === totalPages"
                class="px-3 py-1 text-xs border border-gray-300 dark:border-zinc-700 rounded-md 
                text-gray-600 dark:text-zinc-300 
                bg-white dark:bg-zinc-900 
                transition 
                hover:bg-[#0F6E8C] hover:text-white hover:border-[#0F6E8C]
                dark:hover:bg-[#173f4b] dark:hover:text-yellow-200 dark:hover:border-[#0F6E8C]
                disabled:opacity-60 disabled:cursor-not-allowed">
                Next
            </button>
        </div>
    </div>
</div>
