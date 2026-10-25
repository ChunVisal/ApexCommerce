@extends('layouts.app')

@section('content')
    @include('admin.stock-movement.scripts')
    <div class="" x-data="movementPage()">

        {{-- Header Action Row Configuration --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">Stock Movements</h1>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                    {{ \Carbon\Carbon::parse($start)->format('M d, Y') }} —
                    {{ \Carbon\Carbon::parse($end)->format('M d, Y') }}
                </p>
            </div>
            <div class="flex gap-2 items-center">
                <x-date-range-picker route="admin.inventory.stock-movements" />
                <x-export-button :route="route('admin.inventory.stock-movements.export')" />
            </div>
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

            {{-- Category --}}
            <div class="relative">
                <select x-model="categoryFilter"
                    class="bg-white dark:bg-zinc-900 bg-none appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->name }}">
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down
                    class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none"
                    stroke-width="2" />
            </div>

            {{-- Status/Type --}}
            <x-filter-select model="filterType">
                <option value="">All Types</option>
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
            </x-filter-select>

            {{-- Status/Type --}}
            <x-filter-select model="filterUser">
                <option value="">All Users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->name }}">
                        {{ $user->name }}
                    </option>
                @endforeach
            </x-filter-select>
        </div>

        {{-- Premium Scannable Table Component Container Block --}}
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
            <div class="scroll-smooth table-scroll overflow-auto max-h-[600px]" x-ref="tableBody">
                <x-table rows="paginatedMovements" as="stockmovement">
                    {{-- Date Column Field Element --}}
                    <td class=" text-left text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                        x-text="new Date(stockmovement.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) + ' ' + new Date(stockmovement.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })">
                    </td>

                    {{-- Product + Category Visual Hierarchy Mapping row --}}
                    <td class="py-3 items-center">
                        <div class="text-left">
                            <p class="font-medium text-gray-800 dark:text-zinc-200 text-sm leading-tight"
                                x-text="stockmovement.product?.name || '-'">
                            </p>
                            <p class="text-xs text-gray-400 dark:text-zinc-500 mt-0.5"
                                x-text="stockmovement.product?.category?.name || '-'">
                            </p>
                        </div>
                    </td>

                    {{-- Status Badge dynamic Element Block Row Layout --}}
                    <td class="text-center whitespace-nowrap">
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                            :class="stockmovement.type === 'in' ?
                                'bg-green-50 dark:bg-green-950/30 text-green-600 dark:text-green-400' :
                                'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400'"
                            x-text="stockmovement.type === 'in' ? 'IN' : 'OUT'">
                        </span>
                    </td>

                    {{-- Quantities Numeric Text Element --}}
                    <td class="text-center font-semibold text-gray-800 dark:text-zinc-200 whitespace-nowrap">
                        <span x-text="stockmovement.dynamic_quantity_rendered ?? stockmovement.quantity"></span><span
                            class="text-xs font-bold text-gray-800 dark:text-zinc-200 whitespace-nowrap lowercase"
                            x-show="stockmovement.product?.has_uom" x-text="stockmovement.product?.base_unit_name">
                        </span>
                    </td>

                    {{-- Balance Numeric Text Element --}}
                    <td class="text-center font-semibold text-gray-800 dark:text-zinc-200 whitespace-nowrap">
                        <span x-text="stockmovement.balance ?? 0"></span>
                    </td>

                    {{-- Context Statement Reason Element row field --}}
                    <td class="text-xs text-center text-gray-600 dark:text-zinc-400 font-medium">
                        <p class="max-w-[200px] break-words line-clamp-2" x-text="stockmovement.reason || '-'">
                        </p>
                    </td>

                    {{-- Reference Numeric Text Element --}}
                    <td class="text-[12px] text-center font-medium text-gray-800 dark:text-zinc-300 whitespace-nowrap">
                        <span x-text="stockmovement.reference || '-'"></span>
                    </td>

                    {{-- Authorized User Metadata Structure Layout --}}
                    <td class="text-xs">
                        <div class="flex justify-end">
                            <div class="text-left">
                                <p class="font-medium text-gray-800 dark:text-zinc-300"
                                    x-text="stockmovement.user?.name || '-'"></p>
                                <p class="text-gray-400 dark:text-zinc-500 scale-95 origin-left mt-0.5"
                                    x-text="stockmovement.user?.email || '-'"></p>
                            </div>
                        </div>
                    </td>
                </x-table>

                {{-- Empty state: filter/search removed everything --}}
                <div x-show="stockmovements.length > 0 && filteredMovements.length === 0">
                    <div
                        class="text-center py-16 bg-white dark:bg-zinc-900 max-w-xs mx-auto flex flex-col items-center justify-center">
                        <div
                            class="w-12 h-12 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                            <x-heroicon-o-currency-dollar class="w-6 h-6 text-gray-400 dark:text-zinc-500" />
                        </div>
                        {{-- Typography hierarchy --}}
                        <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                            No Results Found
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[200px] leading-relaxed">
                            There are no stock movements matching this filter.
                        </p>
                    </div>
                </div>

                {{-- Empty State Row --}}
                <div x-show="stockmovements.length === 0">
                    <div
                        class="text-center py-16 bg-white dark:bg-zinc-900 max-w-xs mx-auto flex flex-col items-center justify-center">
                        {{-- Circular minimalist movement path icon container --}}
                        <div
                            class="w-11 h-11 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                            <x-heroicon-o-arrows-right-left class="w-5 h-5 text-gray-400 dark:text-zinc-500" />
                        </div>
                        {{-- Typography hierarchy --}}
                        <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                            No Stock Movements
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[200px] leading-relaxed">
                            There are no registered transaction actions or inventory shifts for this filter.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Alpine Pagination --}}
            <x-pagination />
        </div>
    </div>
@endsection
