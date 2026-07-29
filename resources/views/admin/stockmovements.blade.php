@extends('layouts.app')

@section('content')
    <div class="p-5" x-data="movementPage()">
        {{-- Header Action Row Configuration --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">Stock Movements</h1>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                    {{ \Carbon\Carbon::parse($start)->format('M d, Y') }} —
                    {{ \Carbon\Carbon::parse($end)->format('M d, Y') }}
                </p>
            </div>
            <a href="{{ route('admin.stockmovement.export') }}"
                class="bg-white dark:bg-zinc-900 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-800 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                Export
            </a>
        </div>

        {{-- Full-Width Responsive Search + Filter Toolbar Grid (Matching Sample Styles & Size) --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">

            {{-- Search Input with Reason Dropdown --}}
            <div class="relative flex-1" x-data="{
                reasonOpen: false,
                reasonResults: [],
                allReasons: ['All Reasons', 'Restock', 'Customer Return', 'Damaged', 'Stock Count Correction', 'Transfer', 'Initial Stock', 'Lost or Stolen', 'Loss: Theft', 'Accident', 'Other']
            }">
                <i
                    class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
                <input type="text" x-model="searchQuery" @input="applyFilters(); toggleClearButton()"
                    @click="reasonResults = allReasons; reasonOpen = true"
                    @input.debounce.200="
    const query = $el.value.toLowerCase();
    reasonResults = query ? allReasons.filter(r => r.toLowerCase().includes(query)) : allReasons;
    reasonOpen = true;
    applyFilters(); 
"
                    placeholder="Search products or select reason..."
                    class="w-full pl-8 pr-8 py-1.5 text-xs border border-gray-300 dark:border-zinc-800 rounded-md bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                <button type="button" id="clearSearch" style="display:none;"
                    @click="searchQuery = ''; applyFilters(); toggleClearButton()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
                    ✕
                </button>

                {{-- Reason Dropdown --}}
                <div x-show="reasonOpen && reasonResults.length > 0" @click.outside="reasonOpen = false" x-cloak
                    class="tab-container absolute left-0 right-0 top-full mt-1 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-lg z-20 max-h-[200px] overflow-y-auto">
                    <template x-for="reason in reasonResults" :key="reason"">
                        <div @mousedown.prevent="searchQuery = reason === 'All Reasons' ? '' : reason; reasonOpen = false; applyFilters()"
                            class=" px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer">
                            <span x-text="reason"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Date Range Dropdown Control Component --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="h-[30px] bg-white dark:bg-zinc-900 flex items-center text-xs gap-2 px-3 border border-gray-300 dark:border-zinc-800 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800/60 text-gray-700 dark:text-zinc-300 transition-colors whitespace-nowrap">
                    <i class="fa-regular fa-calendar text-gray-400 dark:text-zinc-500"></i>
                    <span>
                        {{ \Carbon\Carbon::parse(request('start_date', now()->subDays(14)))->format('M d, Y') }}
                        -
                        {{ \Carbon\Carbon::parse(request('end_date', now()))->format('M d, Y') }}
                    </span>
                    <i class="fa-solid fa-chevron-down text-gray-400 dark:text-zinc-500 text-[10px]"></i>
                </button>

                <div x-show="open" @click.outside="open = false" x-cloak
                    class="absolute right-0 mt-1.5 w-60 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-lg dark:shadow-zinc-950/50 z-30 p-3">
                    {{-- Action Dropdown Presets Mapping Elements --}}
                    <div class="space-y-1 mb-3">
                        <a href="{{ route('admin.inventory.movements', ['start_date' => now()->subDays(6)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Last
                            7 days</a>
                        <a href="{{ route('admin.inventory.movements', ['start_date' => now()->subDays(14)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Last
                            15 days</a>
                        <a href="{{ route('admin.inventory.movements', ['start_date' => now()->subDays(29)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Last
                            30 days</a>
                        <a href="{{ route('admin.inventory.movements', ['start_date' => now()->subDays(89)->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                            class="block px-2 py-1.5 text-xs rounded text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800/60 transition-colors">Last
                            90 days</a>
                    </div>

                    <div class="border-t border-gray-200 dark:border-zinc-800 pt-3">
                        <p class="text-[11px] font-semibold text-gray-500 dark:text-zinc-400 mb-2">Custom range</p>
                        <form action="{{ route('admin.inventory.movements') }}" method="GET" class="space-y-2">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="product_id" value="{{ request('product_id') }}">
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="[&::-webkit-calendar-picker-indicator]:dark:invert w-full text-xs border border-gray-300 dark:border-zinc-800 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 focus:outline-none focus:border-[#0F6E8C]">
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="[&::-webkit-calendar-picker-indicator]:dark:invert w-full text-xs border border-gray-300 dark:border-zinc-800 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 focus:outline-none focus:border-[#0F6E8C]">
                            <button type="submit"
                                class="w-full px-3 py-1.5 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition-colors">
                                Apply
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Category Filter Dropdown --}}
            <div class="relative">
                <select id="CategoryFilter" x-model="filterCategory" @change="applyFilters()"
                    class=" bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ request('categories_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down
                    class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
            </div>

            {{-- Type Filter Dropdown --}}
            <div class="relative ">
                <select id="typeFilter" x-model="filterType" @change="applyFilters()"
                    class=" bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                    <option value="">All Types</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                </select>
                <x-heroicon-o-chevron-down
                    class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
            </div>

        </div>

        {{-- Premium Scannable Table Component Container Block --}}
        <div
            class="bg-white dark:bg-zinc-900 pb-4 px-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
            <div class="scroll-smooth table-scroll overflow-auto max-h-[600px]" x-ref="tableBody">
                <table class="w-full text-sm text-left">
                    <thead class="sticky top-0 z-10 bg-white dark:bg-zinc-900">
                        <tr
                            class="text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800/80 bg-gray-50/50 dark:bg-zinc-900/50">
                            <th class="py-3 px-4 font-medium whitespace-nowrap">Date</th>
                            <th class="py-3 px-4 font-medium min-w-[165px]">Product</th>
                            <th class="py-3 px-4 font-medium text-left">Type</th>
                            <th class="py-3 px-4 font-medium text-center">Qty</th>
                            <th class="py-3 px-4 font-medium text-right">Balance</th>
                            <th class="py-3 font-medium text-center min-w-[150px]">Reason</th>
                            <th class="py-3 px-4 font-medium text-center">Reference</th>
                            <th class="py-3 px-4 font-medium text-left min-w-[140px]">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                        <template x-for="movement in paginatedMovements" :key="movement.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition-colors">
                                {{-- Date Column Field Element --}}
                                <td class="py-3 px-4 text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                                    x-text="new Date(movement.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) + ' ' + new Date(movement.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false })">
                                </td>

                                {{-- Product + Category Visual Hierarchy Mapping row --}}
                                <td class="py-3 px-4">
                                    <div class="min-w-[100px]">
                                        <p class="font-medium text-gray-800 dark:text-zinc-200 text-sm leading-tight"
                                            x-text="movement.product?.name || '-'">
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5"
                                            x-text="movement.product?.category?.name || '-'">
                                        </p>
                                    </div>
                                </td>

                                {{-- Status Badge dynamic Element Block Row Layout --}}
                                <td class="py-3 px-4 text-left whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                                        :class="movement.type === 'in' ?
                                            'bg-green-50 dark:bg-green-950/30 text-green-600 dark:text-green-400' :
                                            'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400'"
                                        x-text="movement.type === 'in' ? 'IN' : 'OUT'">
                                    </span>
                                </td>

                                {{-- Quantities Numeric Text Element --}}
                                <td
                                    class="py-3 px-4 text-center font-semibold text-gray-800 dark:text-zinc-200 whitespace-nowrap">
                                    <span x-text="movement.dynamic_quantity_rendered ?? movement.quantity"></span>
                                    <span
                                        class="text-xs font-bold text-gray-800 dark:text-zinc-200 whitespace-nowrap lowercase"
                                        x-show="movement.product?.has_uom" x-text="movement.product?.base_unit_name">
                                    </span>
                                </td>

                                {{-- Balance Numeric Text Element --}}
                                <td
                                    class="py-3 px-4 text-center font-semibold text-gray-800 dark:text-zinc-200 whitespace-nowrap">
                                    <span x-text="movement.balance ?? 0"></span>
                                </td>

                                {{-- Context Statement Reason Element row field --}}
                                <td class="py-3 pl-2 text-xs text-left text-gray-600 dark:text-zinc-400 font-medium">
                                    <p class="max-w-[200px] break-words line-clamp-2" x-text="movement.reason || '-'">
                                    </p>
                                </td>

                                {{-- Reference Numeric Text Element --}}
                                <td
                                    class="text-[12px] py-3 pl-2 text-center font-medium text-gray-800 dark:text-zinc-300 whitespace-nowrap">
                                    <span x-text="movement.reference || '-'"></span>
                                </td>

                                {{-- Authorized User Metadata Structure Layout --}}
                                <td class="py-3 px-4 text-xs text-left">
                                    <div class="min-w-[140px]">
                                        <p class="font-medium text-gray-800 dark:text-zinc-300"
                                            x-text="movement.user?.name || '-'">
                                        </p>
                                        <p class="text-gray-400 dark:text-zinc-500 scale-95 origin-left mt-0.5"
                                            x-text="movement.user?.email || '-'">
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- Empty State Row --}}
                        <tr x-show="filteredMovements.length === 0">
                            <td colspan="8" class="text-center py-16 bg-white dark:bg-zinc-900">
                                <div class="max-w-xs mx-auto flex flex-col items-center justify-center">
                                    {{-- Circular minimalist movement path icon container --}}
                                    <div
                                        class="w-11 h-11 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-zinc-500" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                        </svg>
                                    </div>

                                    {{-- Typography hierarchy --}}
                                    <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                                        No Stock Movements
                                    </p>
                                    <p
                                        class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[200px] leading-relaxed">
                                        There are no registered transaction actions or inventory shifts for this filter.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Alpine Pagination --}}
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-200 dark:border-zinc-800">
                <p class="text-xs text-gray-500 dark:text-zinc-400" x-text="showingText"></p>
                <div class="flex items-center gap-1">
                    <button @click="prevPage()" :disabled="currentPage === 1" type="button"
                        class="px-3 py-1 text-xs border border-gray-300 dark:border-zinc-700 rounded-md text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition disabled:opacity-50">Previous</button>
                    <template x-for="page in pageNumbers" :key="page">
                        <div>
                            <button x-show="page !== '...'" @click="goToPage(page)" type="button"
                                :class="currentPage === page ? 'bg-[#0F6E8C] text-white' :
                                    'border border-gray-300 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700'"
                                class="px-3 py-1 text-xs rounded-md transition">
                                <span x-text="page"></span>
                            </button>
                            <span x-show="page === '...'" class="px-2 text-gray-400">...</span>
                        </div>
                    </template>

                    <button @click="nextPage()" :disabled="currentPage === totalPages" type="button"
                        class="px-3 py-1 text-xs border border-gray-300 dark:border-zinc-700 rounded-md text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function movementPage() {
        return {
            movements: @json($movements),
            searchQuery: '',
            filterType: '',
            filterCategory: '',
            currentPage: 1,
            perPage: 20,

            get filteredMovements() {
                let result = [...this.movements];
                if (this.filterType) result = result.filter(m => m.type === this.filterType);
                if (this.filterCategory) result = result.filter(m => m.product?.category_id == this.filterCategory);
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(m => (m.product?.name || '').toLowerCase().includes(q) || (m.reason ||
                        '').toLowerCase().includes(q));
                }
                return result;
            },

            // Inside movementPage(), add:
            toggleClearButton() {
                const btn = document.getElementById('clearSearch');
                if (btn) btn.style.display = this.searchQuery.length > 0 ? 'block' : 'none';
            },

            get totalPages() {
                return Math.ceil(this.filteredMovements.length / this.perPage);
            },
            get paginatedMovements() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredMovements.slice(start, start + this.perPage);
            },
            get showingText() {
                const start = (this.currentPage - 1) * this.perPage + 1;
                const end = Math.min(this.currentPage * this.perPage, this.filteredMovements.length);
                return `Showing ${start}-${end} of ${this.filteredMovements.length} entries`;
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
            applyFilters() {
                this.currentPage = 1;
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
        };
    }
</script>
