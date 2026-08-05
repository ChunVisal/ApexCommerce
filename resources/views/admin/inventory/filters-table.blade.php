<!-- Filters (Kept exactly to your design layout) -->
<div class="flex flex-wrap items-center gap-3 mb-4">
    <div class="relative flex-1 id="searchSection" class="min-w-[200px]">
        <i
            class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
        <input x-model="searchQuery" type="text" placeholder="Search by name, categories or code..."
            class="w-full pl-8 pr-8 py-1.5 text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md focus:outline-none focus:ring-1 focus:ring-p placeholder-gray-400 dark:placeholder-zinc-500">
        <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterProducts()"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
            ✕
        </button>
    </div>
    <div class="relative">
        <select x-model="categoryFilter"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->name }}">{{ $category->name }} ({{ (int) $category->total_stock }})
                </option>
            @endforeach
        </select>
        <x-heroicon-o-chevron-down
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
    </div>
    {{-- Status --}}
    <div class="relative">
        <select x-model="statusFilter"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-p cursor-pointer">
            <option value="all">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor"
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
    <div class="relative">
        <select x-model="stockFilter"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
            <option value="all">All Stock</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
            <option value="normal">In Stock</option>
        </select>
        <x-heroicon-o-chevron-down
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
    </div>
    <button @click="openAdjust()"
        class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-medium text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition">
        <i class="fa-solid fa-plus"></i> Stock Adjustment
    </button>
</div>

<!-- Stock Table -->
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 mb-4">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800 whitespace-nowrap">
                    <th class="flex gap-1 pb-2 pr-4 font-medium">Product
                    </th>
                    <th class="pb-2 px-4 font-medium">Category</th>
                    <th class="pb-2 px-4 font-medium text-center">Current Stock</th>
                    <th class="pb-2 px-4 font-medium text-center">Reorder Level</th>
                    <th class="pb-2 px-4 font-medium text-center">Status</th>
                    <th class="pb-2 px-4 font-medium">Last Updated</th>
                    <th class="pb-2 pl-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                <template x-for="product in filteredProducts" :key="product.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3">
                                <img :src="product.image ||
                                    'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                                    class="w-12 h-12 bg-[#0F6E8C]/10 dark:bg-[#0F6E8C]/20 rounded-xs shrink-0 object-cover" />
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-zinc-200 line-clamp-3"
                                        x-text="product.name"></p>
                                    <p class="text-xs text-gray-400 dark:text-zinc-500" x-text="product.code"></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 pl-4 text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                            x-text="product.category?.name || 'Unassigned'">
                        </td>
                        <td class="py-3 px-3 text-center whitespace-nowrap">
                            <template x-if="product.stock_quantity <= 0">
                                <span
                                    class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400">
                                    Out of stock
                                </span>
                            </template>
                            <template
                                x-if="product.stock_quantity > 0 && product.stock_quantity < product.low_stock_threshold">
                                <span
                                    class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                                    <span x-text="product.stock_quantity"></span>
                                    <span x-show="product.has_uom" x-text="product.base_unit_name"
                                        class="lowercase"></span> Low

                                </span>
                            </template>
                            <template x-if="product.stock_quantity >= product.low_stock_threshold">
                                <span
                                    class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400">
                                    <span x-text="product.stock_quantity"></span>
                                    <span x-show="product.has_uom" x-text="product.base_unit_name"
                                        class="lowercase"></span>
                                </span>
                            </template>
                        </td>
                        <td class="py-3 text-center text-gray-500 dark:text-zinc-400"
                            x-text="product.low_stock_threshold">
                        </td>
                        <td class="py-3 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full"
                                :class="product.status === 'active' ?
                                    'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                    'bg-gray-100 text-gray-500 dark:bg-zinc-700 dark:text-zinc-400'"
                                x-text="product.status.charAt(0).toUpperCase() + product.status.slice(1)">
                            </span>
                        </td>
                        <td class="py-3 pl-4 text-gray-500 dark:text-zinc-500 text-xs whitespace-nowrap"
                            x-text="product.updated_at ? new Date(product.updated_at).toLocaleDateString('en-US', {hour: 'numeric', minute: '2-digit', month:'short', day:'numeric', year:'numeric'}) : '-'">
                        </td>

                        <td class="py-3">
                            <div class="flex items-center justify-end gap-3">
                                {{-- Stock Drop - only for active --}}
                                <template x-if="product.status === 'active'">
                                    <button @click="openStockDrop(product.id)" class="text-p hover:text-blue-600"
                                        title="Drop to Cashier">
                                        <i class="fa-solid fa-truck"></i>
                                    </button>
                                </template>
                                <template x-if="product.status !== 'active'">
                                    <button @click="alert('Product is inactive')"
                                        class="text-gray-300 dark:text-zinc-600 cursor-not-allowed" title="Inactive">
                                        <i class="fa-solid fa-truck"></i>
                                    </button>
                                </template>

                                {{-- Stock Adjustment - always available --}}
                                <button @click="openAdjust(product)"
                                    class="text-gray-400 dark:text-zinc-500 hover:text-[#0F6E8C] dark:hover:text-[#0F6E8C]"
                                    title="Adjust Stock">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>

                <template x-if="filteredProducts.length === 0">
                    <tr>
                        <td colspan="9" class="text-center py-20 bg-white dark:bg-zinc-900">
                            <div class="max-w-xs mx-auto flex flex-col items-center justify-center">
                                {{-- Stacked empty-box icon container matching premium system theme --}}
                                <div
                                    class="w-14 h-14 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-zinc-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                </div>
                                {{-- Structured typography layout --}}
                                <p class="text-md font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                                    No matching products
                                </p>
                                <p
                                    class="text-[12px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[190px] leading-relaxed">
                                    Try adjusting your search criteria or filters to locate items.
                                </p>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>

            <tr id="noCategoryRow" class="" style="display:none;">
                <td colspan="8" class="text-center py-16">
                    <div class="flex flex-col items-center justify-center">
                        {{-- Icon --}}
                        <div
                            class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400 dark:text-zinc-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>

                        {{-- Text --}}
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">
                            No products found in Inventory
                        </h3>
                        <p class="text-xs text-gray-400 dark:text-zinc-500 max-w-xs">
                            Try adjusting your search or filter to find what you're looking for.
                        </p>

                    </div>
                </td>
            </tr>

        </table>
    </div>
</div>
