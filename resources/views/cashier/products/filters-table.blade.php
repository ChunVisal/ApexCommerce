{{-- resources/views/cashier/partials/products/filters-table.blade.php --}}

{{-- Filter Bar --}}
<div class="flex flex-wrap items-center gap-3 mb-4">
    {{-- Search --}}
    <x-search-input placeholder="Search by name, categories, code, or barcode..." />

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
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>

    <x-filter-select model="filterStock">
        <option value="all">All Stock</option>
        <option value="out">Out of Stock</option>
        <option value="low">Low Stock</option>
        <option value="in">In Stock</option>
    </x-filter-select>

    {{-- View Toggle --}}
    <div class="min-w-[120px] relative">
        <button @click="viewMode = viewMode === 'list' ? 'uom' : 'list'; localStorage.setItem('viewMode', viewMode)"
            class="w-full bg-p  py-1.5 text-xs text-gray-200 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md font-medium hover:opacity-90 transition">
            <span x-text="viewMode === 'list' ? 'Switch to UOM' : 'Switch to List'"></span>
        </button>
    </div>
</div>

{{-- Main Master Table Container --}}
<div
    class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60 overflow-hidden">

    {{-- VIEW 1: STANDARD PRODUCTS LIST --}}
    <div x-show="viewMode === 'list'" class="overflow-x-auto">
        <x-table headers="productHeaders" rows="filteredProducts.filter(p => !p.uom_list || p.uom_list.length === 0)"
            as="product">
            <td class="py-3">
                <div class="flex items-center gap-3">
                    <img :src="product.image ??
                        'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                        class="w-14 h-14 rounded-xs object-cover bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-800 shrink-0">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-800 dark:text-zinc-200" x-text="product.name"></p>
                        <p class="text-[11px] text-gray-400" x-text="product.code"></p>
                    </div>
                </div>
            </td>
            <td class="text-xs text-gray-500 dark:text-zinc-400" x-text="product.category_name">
            </td>
            <td class="  text-gray-700 dark:text-zinc-300 font-medium" x-text="product.allocated">
            </td>
            <td class="  text-red-500 font-medium" x-text="product.sold"></td>
            <td class="  font-bold text-green-600" x-text="product.remaining"></td>
            <td class="  font-bold text-red-500" x-text="product.lost ?? 0"></td>
            <td class="  font-semibold text-[#0F6E8C]" x-text="'$' + Number(product.selling_price).toFixed(2)"></td>
            <td class="  font-semibold text-purple-600 dark:text-purple-400"
                x-text="'$' + Number(product.revenue ?? 0).toFixed(2)"></td>

            <td class="">
                <template x-if="product.remaining <= 0">
                    <span
                        class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400">
                        Out of stock
                    </span>
                </template>
                <template x-if="product.remaining > 0 && product.remaining <= product.low_stock_threshold">
                    <span
                        class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                        <span x-text="product.remaining"></span>
                        <span x-show="product.has_uom" x-text="product.base_unit_name" class="lowercase"></span>
                        Low
                    </span>
                </template>
                <template x-if="product.remaining > product.low_stock_threshold">
                    <span
                        class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400">
                        <span x-text="product.remaining"></span>
                        <span x-show="product.has_uom" x-text="product.base_unit_name" class="lowercase"></span>
                    </span>
                </template>
            </td>
            <td class=" text-xs  text-gray-500 dark:text-zinc-400"
                x-text="product.last_drop ? new Date(product.last_drop).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-'">
            </td>

            <td class=" ">
                <button @click="reportLoss(product.id, product.name, product.remaining)"
                    class="text-xs font-medium text-red-500 hover:text-red-600 transition-colors shrink-0">
                    Report
                </button>
            </td>
        </x-table>

        <x-empty-state icon="heroicon-o-archive-box"
            x-show="products.length > 0 && filteredProducts.filter(p => !p.uom_list || p.uom_list.length === 0).length === 0"
            title="No Single Products" description="No products match your current filters." />

        {{-- no data at all --}}
        <x-empty-state icon="heroicon-o-cube" x-show="products.length === 0" title="No products yet"
            description="Products allocated to you will appear here." />
    </div>

    {{-- VIEW 2: REDESIGNED PRODUCTS UOM VIEW --}}
    <div x-show="viewMode === 'uom'">
        <x-table headers="productUomHeaders" rows="filteredProducts.filter(p => p.uom_list && p.uom_list.length > 0)"
            as="product">
            <td class="py-3 whitespace-nowrap">
                <div class="flex items-center gap-3 relative">
                    <img :src="product.image ??
                        'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                        class="w-14 h-14 rounded-sm object-cover bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-800 shrink-0">
                    <!-- Stock status circle -->
                    <span
                        class="absolute -top-1 left-12 w-4 h-4 rounded-full border-2 border-white dark:border-zinc-900"
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
                        <p class="text-[12px] text-gray-400 dark:text-zinc-500 font-mono" x-text="product.code"></p>
                    </div>
                </div>

            </td>

            {{-- Category --}}
            <td class="  font-medium text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                x-text="product.category_name"></td>

            {{-- Base Unit --}}
            <td class="  whitespace-nowrap">
                <span
                    class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40"
                    x-text="product.base_unit_name || 'Unit'">
                </span>
            </td>

            {{-- UOM unit --}}
            <td class=" ">
                <div class="flex items-center justify-center gap-1 flex-wrap">
                    <template x-for="uom in product.uom_list.filter(u => !u.is_default)" :key="uom.id">
                        <div class="relative group">
                            <span
                                class="cursor-pointer px-2 py-0.5 text-[12px] font-medium bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 rounded">
                                <span x-text="uom.name || 'Unit'"></span>
                                (<span x-text="uom.allocated_quantity"></span><span
                                    x-text="product.base_unit_code || product.base_unit_name || 'Unit'"></span>)
                                -
                                $<span x-text="parseFloat(uom.price).toFixed(2)"></span>
                            </span>
                            {{-- Custom tooltip --}}
                            <div
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block whitespace-nowrap px-2 py-1 text-[11px] font-medium text-white bg-gray-900 dark:bg-zinc-700 rounded shadow-lg z-10">
                                <span x-text="uom.description || 'No description'"></span>
                                {{-- little arrow pointing down --}}
                                <div
                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-zinc-700">
                                </div>
                            </div>
                        </div>
                    </template>
                    {{-- Fallback: base unit only, no additional UOMs --}}
                    <span x-show="!product.uom_list || product.uom_list.filter(u => !u.is_default).length === 0"
                        class="px-2 py-0.5 text-[12px] font-medium  bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 rounded">
                        1 <span x-text="product.base_unit_code || product.base_unit_name || 'unit'"></span>
                        -
                        $<span x-text="Number(product.selling_price || 0).toFixed(2)"></span>
                    </span>
                </div>
            </td>

            {{-- Master Data Fields --}}
            <td class="   text-gray-700 dark:text-zinc-300 font-medium whitespace-nowrap" x-text="product.allocated">
            </td>
            <td class="   text-rose-500 dark:text-rose-400 font-semibold whitespace-nowrap" x-text="product.sold">
            </td>
            <td class="   font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap"
                x-text="product.remaining"></td>
            <td class="px-2  font-bold text-red-500" x-text="product.lost ?? 0"></td>
            <td class="   font-semibold text-[#0F6E8C] dark:text-cyan-400 whitespace-nowrap"
                x-text="'$' + Number(product.selling_price).toFixed(2)"></td>
            <td class="   font-semibold text-purple-600 dark:text-purple-400 whitespace-nowrap"
                x-text="'$' + Number(product.revenue ?? 0).toFixed(2)"></td>
            <td class="  text-xs  text-gray-500 dark:text-zinc-400 whitespace-nowrap"
                x-text="product.last_drop ? new Date(product.last_drop).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-'">
            </td>

            {{-- Actions --}}
            <td class=" whitespace-nowrap">
                <button @click="reportLoss(product.id, product.name, product.remaining)"
                    class="text-xs font-medium text-rose-600 hover:text-rose-700 transition-colors shrink-0">
                    Report
                </button>
            </td>
        </x-table>

        {{-- filter removed everything --}}
        <x-empty-state icon="heroicon-o-archive-box"
            x-show="products.length > 0 && filteredProducts.filter(p => p.uom_list && p.uom_list.length > 0).length === 0"
            title="No UOM Products Found"
            description="There are no products (UOM) items that match your search filters." />

        {{-- no data at all --}}
        <x-empty-state icon="heroicon-o-cube" x-show="products.length === 0" title="No products yet"
            description="Products allocated to you will appear here." />
    </div>


</div>
