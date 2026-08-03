{{-- resources/views/cashier/partials/products/filters-table.blade.php --}}

{{-- Filter Bar --}}
<div class="flex flex-wrap items-center gap-3 mb-4">
    {{-- Search --}}
    <div class="relative flex-1 min-w-[200px]">
        <i
            class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
        <input id="search" type="text" x-model="searchQuery" value="{{ request('search') }}"
            placeholder="Search by name, categories, code, or barcode..."
            class="w-full pl-8 pr-8 py-1.5 text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md focus:outline-none focus:ring-1 focus:ring-p placeholder-gray-400 dark:placeholder-zinc-500">
        <button type="button" id="clearSearch" style="display:none;"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
            ✕
        </button>
    </div>

    {{-- Category --}}
    <div class="relative">
        <select x-model="filterCategory"
            class="bg-white dark:bg-zinc-900 bg-none appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-p cursor-pointer">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->name }}">
                    {{ $category->name }} ({{ (int) $category->cashier_remaining }})
                </option>
            @endforeach
        </select>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor"
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>

    {{-- Stock --}}
    <div class="relative">
        <select x-model="filterStock"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-p cursor-pointer">
            <option value="all">All Stock</option>
            <option value="out">Out of Stock</option>
            <option value="low">Low Stock</option>
            <option value="in">In Stock</option>
        </select>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor"
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>

    {{-- View Toggle --}}
    <div class="min-w-[120px] relative">
        <button @click="viewMode = viewMode === 'list' ? 'uom' : 'list'"
            class="w-full bg-p px-3 py-1.5 text-xs text-gray-200 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md font-medium hover:opacity-90 transition">
            <span x-text="viewMode === 'list' ? 'Switch to UOM' : 'Switch to List'"></span>
        </button>
    </div>
</div>

{{-- Main Master Table Container --}}
<div
    class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60 overflow-hidden">

    {{-- VIEW 1: STANDARD PRODUCTS LIST --}}
    <div x-show="viewMode === 'list'" class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50">
                    <th class="py-3 pl-4 pr-2 font-medium">Product</th>
                    <th class="py-3 px-2 font-medium">Category</th>
                    <th class="py-3 px-2 font-medium text-center">Allocated</th>
                    <th class="py-3 px-2 font-medium text-center">Sold</th>
                    <th class="py-3 px-2 font-medium text-center">Remaining</th>
                    <th class="py-3 px-2 font-medium text-right">Price</th>
                    <th class="py-3 px-2 font-medium text-right">Revenue</th>
                    <th class="py-3 px-2 font-medium text-center">Last Drop</th>
                    <th class="py-3 px-2 font-medium text-center">Status</th>
                    <th class="py-3 pr-4 pl-2 font-medium text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60">
                <template x-for="product in filteredProducts.filter(p => !p.uom_list || p.uom_list.length === 0)"
                    :key="product.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pl-4">
                            <div class="flex items-center gap-3">
                                <img :src="product.image ??
                                    'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                                    class="w-12 h-12 rounded-md object-cover bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-800 shrink-0">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-zinc-200 truncate max-w-[200px]"
                                        x-text="product.name"></p>
                                    <p class="text-[11px] text-gray-400" x-text="product.code"></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-2 text-xs text-gray-500 dark:text-zinc-400" x-text="product.category_name">
                        </td>
                        <td class="py-3 px-2 text-center text-gray-700 dark:text-zinc-300 font-medium"
                            x-text="product.allocated"></td>
                        <td class="py-3 px-2 text-center text-red-500 font-medium" x-text="product.sold"></td>
                        <td class="py-3 px-2 text-center font-bold text-green-600" x-text="product.remaining"></td>
                        <td class="py-3 px-2 text-right font-semibold text-[#0F6E8C]"
                            x-text="'$' + Number(product.selling_price).toFixed(2)"></td>
                        <td class="py-3 px-2 text-right font-semibold text-purple-600 dark:text-purple-400"
                            x-text="'$' + Number(product.revenue ?? 0).toFixed(2)"></td>
                        <td class="py-3 px-2 text-xs text-center text-gray-500 dark:text-zinc-400"
                            x-text="product.last_drop ? new Date(product.last_drop).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-'">
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="px-2 py-0.5 text-[10px] rounded-full font-medium"
                                :class="product.remaining > product.low_stock_threshold ?
                                    'bg-green-100 dark:bg-green-400 text-green-600 dark:text-green-900' : (
                                        product.remaining > 0 ?
                                        'bg-amber-100 dark:bg-amber-300 text-amber-500 dark:text-amber-700' :
                                        'bg-red-100 dark:bg-red-300 text-red-700')"
                                x-text="product.remaining > product.low_stock_threshold ? 'In Stock' : (product.remaining > 0 ? 'Low Stock' : 'Out of Stock')"
                                x-text="product.remaining > 5 ? 'In Stock' : (product.remaining > 0 ? 'Low Stock' : 'Out of Stock')">
                            </span>
                        </td>
                        <td class="py-3 pr-4 pl-2 text-center">
                            <button @click="reportLoss(product.id, product.name, product.remaining)"
                                class="text-xs font-medium text-red-500 hover:text-red-600 transition-colors shrink-0">
                                Report Loss
                            </button>
                        </td>
                    </tr>
                </template>

                {{-- Empty State --}}
                <template x-if="filteredProducts.filter(p => !p.uom_list || p.uom_list.length === 0).length === 0">
                    <tr>
                        <td colspan="10" class="text-center py-16 bg-white dark:bg-zinc-900">
                            <div class="max-w-xs mx-auto flex flex-col items-center justify-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-zinc-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                                    No Single Products</p>
                                <p
                                    class="text-[12px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[200px] leading-relaxed">
                                    No non-UOM products match your current filters.
                                </p>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- VIEW 2: REDESIGNED PRODUCTS UOM VIEW --}}
    <div x-show="viewMode === 'uom'"
        class="tab-container overflow-x-auto rounded-lg border border-gray-200 dark:border-zinc-800">
        <table class="w-full text-sm text-left border-collapse">
            <thead>
                <tr
                    class="text-xs text-gray-600 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800 bg-gray-50/70 dark:bg-zinc-900/80">
                    <th class="py-3.5 pl-4 pr-3 font-medium min-w-[220px] whitespace-nowrap">Product
                    </th>
                    <th class="py-3.5 px-3 font-medium min-w-[120px] whitespace-nowrap">Category</th>
                    <th class="py-3.5 font-medium text-center whitespace-nowrap">Base Unit</th>
                    <th class="py-3.5 px-3 font-medium text-center min-w-[180px] whitespace-nowrap">
                        UOM Units</th>
                    <th class="py-3.5 px-3 font-medium text-center whitespace-nowrap">Allocated</th>
                    <th class="py-3.5 px-3 font-medium text-center whitespace-nowrap">Sold</th>
                    <th class="py-3.5 px-3 font-medium text-center whitespace-nowrap">Remaining</th>
                    <th class="py-3.5 px-3 font-medium text-right whitespace-nowrap">Base Price</th>
                    <th class="py-3.5 px-3 font-medium text-right whitespace-nowrap">Revenue</th>
                    <th class="py-3.5 px-3 font-medium text-center whitespace-nowrap">Last Drop</th>

                    <th class="py-3.5 pr-4 pl-3 font-medium text-center whitespace-nowrap">Action
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/60 bg-white dark:bg-zinc-900">
                <template x-for="product in filteredProducts.filter(p => p.uom_list && p.uom_list.length > 0)"
                    :key="product.id">
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-800/40 transition-colors">
                        {{-- Product Details --}}
                        <td class="py-3.5 pl-4 pr-3 whitespace-nowrap">
                            <div class="flex items-center gap-3 relative">
                                <img :src="product.image ??
                                    'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                                    class="w-12 h-12 rounded-md object-cover bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-800 shrink-0">
                                <!-- Stock status circle -->
                                <span
                                    class="absolute -top-1 left-10 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-zinc-900"
                                    :class="product.remaining > product.low_stock_threshold ?
                                        'bg-emerald-400' :
                                        (product.remaining > 0 ?
                                            'bg-amber-400' :
                                            'bg-red-400')"
                                    :title="product.remaining > product.low_stock_threshold ? 'In Stock' : (product.remaining >
                                        0 ? 'Low Stock' : 'Out of Stock')">
                                </span>
                                <div class="min-w-[150px] flex-1">
                                    <p class="line-clamp-2 font-medium text-gray-900 dark:text-zinc-100 max-w-[180px]"
                                        x-text="product.name"></p>
                                    <p class="text-[12px] text-gray-400 dark:text-zinc-500 font-mono"
                                        x-text="product.code"></p>
                                </div>
                            </div>

                        </td>

                        {{-- Category --}}
                        <td class="py-3.5 px-3 font-medium text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                            x-text="product.category_name"></td>

                        {{-- Base Unit --}}
                        <td class="py-3.5 text-center whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40"
                                x-text="product.base_unit_name || 'Unit'">
                            </span>
                        </td>

                        {{-- UOM unit --}}
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <template x-for="uom in product.uom_list.filter(u => !u.is_default)"
                                    :key="uom.id">
                                    <span
                                        class="px-2 py-0.5 text-[12px] font-medium bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 rounded">
                                        <span x-text="uom.name || 'Unit'"></span>
                                        (<span x-text="uom.allocated_quantity"></span><span
                                            x-text="product.base_unit_code || product.base_unit_name || 'Unit'"></span>)
                                        -
                                        $<span x-text="parseFloat(uom.price).toFixed(2)"></span>
                                    </span>
                                </template>
                                {{-- Fallback: base unit only, no additional UOMs --}}
                                <span
                                    x-show="!product.uom_list || product.uom_list.filter(u => !u.is_default).length === 0"
                                    class="px-2 py-0.5 text-[12px] font-medium  bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 rounded">
                                    1 <span x-text="product.base_unit_code || product.base_unit_name || 'unit'"></span>
                                    -
                                    $<span x-text="Number(product.selling_price || 0).toFixed(2)"></span>
                                </span>
                            </div>
                        </td>

                        {{-- Master Data Fields --}}
                        <td class="py-3.5 px-3 text-center text-gray-700 dark:text-zinc-300 font-medium whitespace-nowrap"
                            x-text="product.allocated"></td>
                        <td class="py-3.5 px-3 text-center text-rose-500 dark:text-rose-400 font-semibold whitespace-nowrap"
                            x-text="product.sold"></td>
                        <td class="py-3.5 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap"
                            x-text="product.remaining"></td>
                        <td class="py-3.5 px-3 text-right font-semibold text-[#0F6E8C] dark:text-cyan-400 whitespace-nowrap"
                            x-text="'$' + Number(product.selling_price).toFixed(2)"></td>
                        <td class="py-3.5 px-3 text-right font-semibold text-purple-600 dark:text-purple-400 whitespace-nowrap"
                            x-text="'$' + Number(product.revenue ?? 0).toFixed(2)"></td>
                        <td class="py-3.5 px-3 text-xs text-center text-gray-500 dark:text-zinc-400 whitespace-nowrap"
                            x-text="product.last_drop ? new Date(product.last_drop).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-'">
                        </td>

                        {{-- Actions --}}
                        <td class="py-3.5 pr-4 pl-3 text-center whitespace-nowrap">
                            <button @click="reportLoss(product.id, product.name, product.remaining)"
                                class="text-xs font-medium text-rose-600 hover:text-rose-700 transition-colors shrink-0">
                                Report Loss
                            </button>
                        </td>
                    </tr>

                </template>

                {{-- Empty State --}}
                <template x-if="filteredProducts.filter(p => p.uom_list && p.uom_list.length > 0).length === 0">
                    <tr>
                        <td colspan="11" class="text-center py-16 bg-white dark:bg-zinc-900">
                            <div class="max-w-xs mx-auto flex flex-col items-center justify-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-800 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-zinc-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                                    No UOM Products Found</p>
                                <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1 max-w-[220px] leading-relaxed">
                                    There are no multi-unit (UOM) items that match your search filters.
                                </p>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
