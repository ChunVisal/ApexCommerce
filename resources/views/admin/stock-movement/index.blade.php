@extends('layouts.app')

@section('content')
    @include('admin.stock-movement.scripts')
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
            <x-export-button :route="route('admin.stockmovement.export')" />
        </div>

        {{-- Full-Width Responsive Search + Filter Toolbar Grid (Matching Sample Styles & Size) --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">

            {{-- Search Input with Reason Dropdown --}}
            <div class="relative flex-1">
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
                    placeholder="Search products, reference or select reason..."
                    class="w-full pl-8 pr-8 py-1.5 text-xs border border-gray-300 dark:border-zinc-800 rounded-md bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                <button type="button" id="clearSearch" style="display:none;"
                    @click="searchQuery = ''; applyFilters(); toggleClearButton()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
                    ✕
                </button>

                {{-- Reason Dropdown --}}
                <div x-show="reasonOpen && reasonResults.length > 0" @click.outside="reasonOpen = false" x-cloak
                    class="tab-container absolute left-0 right-0 top-full mt-1 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-lg z-20 max-h-[250px] overflow-y-auto">
                    <template x-for="reason in reasonResults" :key="reason"">
                        <div @mousedown.prevent="searchQuery = reason === 'All Reasons' ? '' : reason; reasonOpen = false; applyFilters()"
                            class=" px-3 py-1.5 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer">
                            <span x-text="reason"></span>
                        </div>
                    </template>
                </div>
            </div>

            <x-date-range-picker route="admin.inventory.movements" />

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
                                    x-text="new Date(movement.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) + ' ' + new Date(movement.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })">
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
                                    <span x-text="movement.dynamic_quantity_rendered ?? movement.quantity"></span><span
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
            <x-pagination />
        </div>
    </div>
@endsection
