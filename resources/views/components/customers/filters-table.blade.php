{{-- resources/views/components/customers/filters-table.blade.php --}}

{{-- Segment Filters --}}
{{-- Search & Sort --}}
<div class="flex flex-wrap items-center gap-2 mb-4">
    <x-search-input placeholder="Search by name, email or phone..." />

    <x-filter-select model="sortBy">
        <option value="recent">Most Recent</option>
        <option value="spent">Highest Spent</option>
        <option value="orders">Most Orders</option>
        <option value="code">Code Range</option>
    </x-filter-select>

    <x-filter-select model="filterSegment">
        <option value="">All Customers</option>
        <option value="vip">VIP Members</option>
        <option value="regular">Regular Customers</option>
        <option value="new">New Customers</option>
    </x-filter-select>
</div>

{{-- Customers Table --}}
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <x-table headers="customerHeaders" rows="filteredCustomers" as="customer">
        <td class="py-3 ">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                    :style="'background-color: ' + ['#0F6E8C', '#1a8aa8', '#2563EB', '#7C3AED', '#059669',
                        '#D97706'
                    ][Math.floor(Math.random() * 6)]">
                    <span x-text="customer.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2)"></span>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-gray-800 dark:text-zinc-200 truncate" x-text="customer.name"></p>
                    <p class="text-xs text-gray-400 truncate" x-text="customer.email"></p>
                </div>
            </div>
        </td>

        <td class=" text-md font-mono text-gray-700 dark:text-zinc-300" x-text="customer.code"></td>
        <td class=" text-xs text-gray-500 dark:text-zinc-400 truncate" x-text="customer.phone">
        </td>

        <td class=" whitespace-nowrap">
            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full"
                :class="{
                    'bg-yellow-50 dark:bg-yellow-950/40 text-yellow-700 dark:text-yellow-300': customer
                        .segment === 'vip',
                    'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-400': customer
                        .segment === 'regular',
                    'bg-green-50 dark:bg-green-950 text-green-700 dark:text-green-400': customer
                        .segment === 'new'
                }"
                x-text="customer.segment.toUpperCase()">
            </span>
        </td>

        <td class=" font-medium text-gray-700 dark:text-zinc-300" x-text="customer.total_orders">
        </td>
        <td class=" font-semibold text-gray-700 dark:text-zinc-300">$<span
                x-text="parseFloat(customer.total_spent || 0).toFixed(2)"></span></td>
        <td class=" text-xs text-gray-500 dark:text-zinc-400"
            x-text="customer.last_order_at ? new Date(customer.last_order_at).toLocaleDateString('en-US', {hour: 'numeric', minute: 'numeric', month:'short', day:'numeric', year:'numeric'}) : '-'">
        </td>
        <td class="">
            <div class="flex items-center justify-end gap-2">
                @if (auth()->user()->role === 'cashier')
                    <button @click="editCustomer(customer)" type="button"
                        class="text-gray-400 dark:text-zinc-500 hover:text-[#0F6E8C] transition-colors" title="Edit">
                        <x-heroicon-m-pencil-square class="w-[21px] h-[21px] mb-1" />
                    </button>
                @endif
                <button @click="openCustomerDetail(customer.id)" type="button"
                    class="text-yellow-400 hover:text-yellow-500 transition-colors" title="Receipt">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </button>
            </div>
        </td>
    </x-table>

    {{-- Empty state for filter/searching --}}
    <x-empty-state icon="heroicon-o-users" x-show="customers.length > 0 && filteredCustomers.length === 0"
        title="No customers found" description="No customers match your filters." />

    {{-- Empty state, no customers yet --}}
    <x-empty-state icon="heroicon-o-users" x-show="customers.length === 0" title="No customers exist yet"
        description="Get started by adding your first customer." />

    {{-- Alpine Pagination --}}
    <x-pagination />

</div>
