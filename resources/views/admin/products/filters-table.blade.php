{{-- Title Row --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Products</h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400">Manage your PC component catalog and stock</p>
        <div class="flex gap-4 mt-1">
            <span class="text-xs text-gray-600 dark:text-zinc-300">
                Total Products: <strong>{{ $products->count() }}</strong>
            </span>
            <span class="text-xs text-gray-600 dark:text-zinc-300">
                Total Stock: <strong>{{ $products->sum('stock_quantity') }}</strong>
            </span>
        </div>
    </div>
    <div class="items-center flex gap-4">
        <button @click="openAdd()"
            class="mt-3 sm:mt-0 inline-flex justify-center items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-p rounded-md hover:bg-[#0c5972] transition">
            <i class="fa-solid fa-plus"></i>
            <span>Add Product</span>
        </button>

        <div class="flex items-center border border-gray-300 dark:border-zinc-700 rounded-md overflow-hidden">
            <button @click="viewMode = 'table'"
                :class="viewMode === 'table' ? 'bg-p text-white' : 'bg-white dark:bg-zinc-900 text-gray-500'"
                class="px-3 py-2">
                <x-heroicon-m-list-bullet class="w-4 h-4" />
            </button>
            <button @click="viewMode = 'grid'"
                :class="viewMode === 'grid' ? 'bg-p text-white' : 'bg-white dark:bg-zinc-900 text-gray-500'"
                class="px-3 py-2 border-l border-gray-300 dark:border-zinc-700">
                <x-heroicon-m-squares-2x2 class="w-4 h-4" />
            </button>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="flex flex-wrap items-center gap-3 mb-4">

    {{-- Search --}}
    <div class="relative flex-1 min-w-[200px]">
        <i
            class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
        <input type="text" x-model="searchQuery" placeholder="Search by name, categories, code, or barcode..."
            class="w-full pl-8 pr-8 py-1.5 text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md focus:outline-none focus:ring-1 focus:ring-p placeholder-gray-400 dark:placeholder-zinc-500">
        <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterProducts()"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
            ✕
        </button>
    </div>

    {{-- Category --}}
    <div class="relative">
        <select x-model="categoryFilter"
            class="bg-white dark:bg-zinc-900 bg-none appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-p cursor-pointer">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->name }}">
                    {{ $category->name }} ({{ $category->total_stock }})
                </option>
            @endforeach
        </select>
        <x-heroicon-o-chevron-down
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none"
            stroke-width="2" />
    </div>

    {{-- Status --}}
    <div class="relative">
        <select x-model="statusFilter"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-p cursor-pointer">
            <option value="all">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
        <x-heroicon-o-chevron-down
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none"
            stroke-width="2" />
    </div>

    {{-- Stock --}}
    <div class="relative">
        <select x-model="stockFilter"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs text-gray-800 dark:text-zinc-200 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-p cursor-pointer">
            <option value="all">All Stock</option>
            <option value="out">Out of Stock</option>
            <option value="low">Low Stock</option>
            <option value="in">In Stock</option>
        </select>
        <x-heroicon-o-chevron-down
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none"
            stroke-width="2" />
    </div>

    {{-- Bulk Action Bar --}}
    {{-- <div id="bulkBar" style="display:none;"
        class="flex items-center justify-between pl-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/60 rounded-md">
        <span class="text-xs text-red-600 dark:text-red-400 font-medium mr-4">
            <span id="bulkCount">0</span> selected
        </span>
        <div class="flex items-center">
            <button onclick="bulkDelete()"
                class="px-3 py-[4.5px] text-[12px] font-medium text-white bg-red-500 hover:bg-red-600 transition">
                Delete Selected
            </button>
            <button onclick="cancelBulkMode()"
                class="px-3 py-[4.5px] text-[12px] font-medium text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-600 rounded-r-md">
                Cancel
            </button>
        </div>
    </div> --}}

</div>

{{-- Table --}}
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60">
    <div class="overflow-x-auto">
        <div x-show="viewMode === 'table'">
            <table class="w-full text-sm">
                <thead>
                    <tr
                        class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                        <th class="pb-2 pr-4 font-medium">Product</th>
                        <th class="pb-2 px-4 font-medium">Category</th>
                        <th class="pb-2 px-4 font-medium text-right">Price</th>
                        <th class="pb-2 px-4 font-medium text-center">Stock</th>
                        <th class="pb-2 px-4 font-medium">Barcode</th>
                        <th class="pb-2 px-4 font-medium text-center">Status</th>
                        <th class="pb-2 px-4 font-medium">Date</th>
                        <th class="pb-2 pl-4 font-medium text-right">Actions</th>
                        {{-- <th class="pb-2 px-4 font-medium">
                            <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes(this)"
                                class="rounded border-gray-300 dark:border-zinc-600 cursor-pointer">
                        </th> --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">

                            {{-- Product Image & Name --}}
                            <td class="py-3 pr-4">
                                <div class="flex items-center gap-3">
                                    <img :src="product.image ||
                                        'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                                        class="w-14 h-14 bg-p/10 dark:bg-p/20 rounded-xs shrink-0 object-cover" />
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-zinc-200 line-clamp-3"
                                            x-text="product.name"></p>
                                        <p class="text-xs text-gray-400 dark:text-zinc-500" x-text="product.code"></p>
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="py-3 px-4 text-gray-600 dark:text-zinc-400" x-text="product.category?.name"></td>

                            {{-- Selling Price --}}
                            <td class="py-3 px-4 text-right font-medium text-gray-800 dark:text-zinc-200"
                                x-text="'$' + Number(product.selling_price).toFixed(2)"></td>

                            {{-- Stock Status Badge (Alpine Version) --}}
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <template x-if="product.stock_quantity == 0">
                                    <span
                                        class="px-2 py-0.5 text-[12px] font-semibold rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400">
                                        Out of stock
                                    </span>
                                </template>
                                <template x-if="product.stock_quantity <= product.low_stock_threshold">
                                    <span
                                        class="px-2 py-0.5 text-[12px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                                        <span x-text="product.stock_quantity"></span> Low
                                    </span>
                                </template>
                                <template x-if="product.stock_quantity > product.low_stock_threshold">
                                    <span
                                        class="px-2 py-0.5 text-[12px] font-semibold rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400">
                                        <span x-text="product.stock_quantity"></span>
                                    </span>
                                </template>
                            </td>

                            {{-- Barcode --}}
                            <td class="py-3 pr-4 text-gray-500 dark:text-zinc-500 text-xs" x-text="product.barcode">
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3
                                text-center">
                                <span class="px-2 py-0.5 text-[12px] font-semibold rounded-full capitalize"
                                    :class="product.status === 'active' ?
                                        'bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400' :
                                        'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-500'"
                                    x-text="product.status">
                                </span>
                            </td>

                            {{-- Timestamps --}}
                            <td class="py-3 px-4 text-xs whitespace-nowrap">
                                <p class="text-gray-500 dark:text-zinc-500">Created
                                    <label class="text-gray-600 dark:text-zinc-500 font-semibold"
                                        x-text="new Date(product.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></label>
                                </p>
                                <p class="text-gray-500 dark:text-zinc-500">Updated
                                    <label class="text-gray-600 dark:text-zinc-500 font-semibold"
                                        x-text="new Date(product.updated_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></label>
                                </p>
                            </td>

                            {{-- Actions --}}
                            <td class="py-3 pl-2">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(product)"
                                        class="text-gray-400 dark:text-zinc-500 hover:text-p dark:hover:text-p"
                                        title="Edit">
                                        <x-heroicon-m-pencil-square class="w-5 h-5" />
                                    </button>
                                    <button @click="deleteProduct(product.id, $el)"
                                        class="trash-btn text-red-500 hover:text-red-600" title="Delete">
                                        <x-heroicon-m-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>

                            {{-- Bulk Checkbox
                            <td class="pl-4">
                                <input type="checkbox" class="bulk-checkbox rounded border-gray-300 cursor-pointer"
                                    :data-id="product.id" @change="updateBulkBar()">
                            </td> --}}
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        @include('admin.products.grid-view')

        {{-- Empty state for filter/searching --}}
        <div x-show="products.length > 0 && filteredProducts.length === 0">
            <div class=" flex flex-col items-center justify-center py-[80px]">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                    <x-heroicon-o-cube class="w-8 h-8 text-gray-400 dark:text-zinc-500" />
                </div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No results
                    Product found
                </h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">Get started by adding your first
                    product.</p>
            </div>
        </div>

        {{-- Empty State if product dont exist yet --}}
        <div x-show="products.length === 0">
            <div class="py-[80px] flex flex-col items-center justify-center">
                <div class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                    <x-heroicon-o-cube class="w-8 h-8 text-gray-400 dark:text-zinc-500" />
                </div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No products
                    eixst yet</h3>
                <p class="text-xs text-gray-400 dark:text-zinc-500">Get started by adding your first
                    product.</p>
                <button @click="openAdd()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add Your First Product
                </button>
            </div>

        </div>

    </div>

</div>
