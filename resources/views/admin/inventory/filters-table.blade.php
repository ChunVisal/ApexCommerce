<!-- Filters (Kept exactly to your design layout) -->
<div class="flex flex-wrap items-center gap-3 mb-4">
    <x-search-input placeholder="Search by name, categories, code, or barcode..." />

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

    <x-filter-select model="statusFilter">
        <option value="all">All Status</option>
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
    </x-filter-select>

    <x-filter-select model="stockFilter">
        <option value="all">All Stock</option>
        <option value="low">Low Stock</option>
        <option value="out">Out of Stock</option>
        <option value="in">In Stock</option>
    </x-filter-select>

    <button @click="openAdjust()"
        class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-medium text-white bg-p rounded-md hover:bg-[#1a627a] transition">
        <i class="fa-solid fa-plus"></i> Stock Adjustment
    </button>
</div>

<!-- Stock Table -->
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60 mb-4">
    <x-table headers="productHeaders" rows="filteredProducts" as="product">
        <td class="text-left py-3">
            <div class="flex items-center gap-3">
                <img :src="product.image ||
                    'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                    class="w-12 h-12 bg-[#0F6E8C]/10 dark:bg-[#0F6E8C]/20 rounded-xs shrink-0 object-cover" />
                <div>
                    <p class="font-medium text-gray-800 dark:text-zinc-200 line-clamp-3" x-text="product.name"></p>
                    <p class="text-xs text-gray-400 dark:text-zinc-500" x-text="product.code"></p>
                </div>
            </div>
        </td>
        <td class="text-center text-gray-600 dark:text-zinc-400 whitespace-nowrap"
            x-text="product.category?.name || 'Unassigned'">
        </td>
        <td class="text-center whitespace-nowrap">
            <template x-if="product.stock_quantity <= 0">
                <span
                    class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400">
                    Out of stock
                </span>
            </template>
            <template x-if="product.stock_quantity > 0 && product.stock_quantity <= product.low_stock_threshold">
                <span
                    class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <span x-text="product.stock_quantity"></span>
                    <span x-show="product.has_uom" x-text="product.base_unit_name" class="lowercase"></span> Low

                </span>
            </template>
            <template x-if="product.stock_quantity > product.low_stock_threshold">
                <span
                    class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400">
                    <span x-text="product.stock_quantity"></span>
                    <span x-show="product.has_uom" x-text="product.base_unit_name" class="lowercase"></span>
                </span>
            </template>
        </td>
        <td class="text-center text-gray-500 dark:text-zinc-400" x-text="product.low_stock_threshold">
        </td>
        <td class="text-center whitespace-nowrap">
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full"
                :class="product.status === 'active' ?
                    'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                    'bg-gray-100 text-gray-500 dark:bg-zinc-700 dark:text-zinc-400'"
                x-text="product.status.charAt(0).toUpperCase() + product.status.slice(1)">
            </span>
        </td>
        <td class="text-center text-gray-500 dark:text-zinc-500 text-xs whitespace-nowrap"
            x-text="product.updated_at ? new Date(product.updated_at).toLocaleDateString('en-US', {hour: 'numeric', minute: '2-digit', month:'short', day:'numeric', year:'numeric'}) : '-'">
        </td>

        <td class="text-right">
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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                    </svg>
                </button>
            </div>
        </td>
    </x-table>

    {{-- Empty state for filter/searching --}}
    <x-empty-state icon="heroicon-o-archive-box" x-show="products.length > 0 && filteredProducts.length === 0"
        title="No matching products" description="Try adjusting your search criteria or filters." />

    {{-- Empty state, no products yet --}}
    <x-empty-state x-show="products.length === 0" title="No products exist yet"
        description="Get started by adding your first product." cta="Add Your First Product" :href="route('admin.products')" />
</div>
