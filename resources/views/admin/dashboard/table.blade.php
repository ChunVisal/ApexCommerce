{{-- resources/views/admin/partials/dashboard/top-tables.blade.php --}}
<div class="p-4 mt-4 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm  border border-gray-200 dark:border-zinc-800/60"
    x-data="{ tab: 'products' }">
    {{-- Tab Header --}}
    <div class="flex flex-wrap items-center justify-between ">
        <div class="flex items-center gap-1">
            <button @click="tab = 'products'"
                :class="tab === 'products' ? 'text-[#0F6E8C] font-semibold' : 'text-gray-500 dark:text-zinc-400 font-medium'"
                class="px-4 py-1.5 text-xs transition">
                Best Selling Products
            </button>
            <button @click="tab = 'categories'"
                :class="tab === 'categories' ? 'text-[#0F6E8C] font-semibold' : 'text-gray-500 dark:text-zinc-400 font-medium'"
                class="px-4 py-1.5 text-xs transition">
                Top Categories
            </button>
        </div>
        <a :href="tab === 'products' ? '{{ route('admin.products') }}' : '{{ route('admin.inventory') }}'"
            class="text-xs text-[#0F6E8C] hover:underline font-medium whitespace-nowrap">
            <span x-text="tab === 'products' ? 'View All Products →' : 'View All Categories →'"></span>
        </a>
    </div>
    <div class="p-4">

        {{-- Products Table --}}
        <div x-show="tab === 'products'" class="overflow-x-auto">
            <x-table headers="headers.topProducts" rows="topProducts" as="product">
                <td>
                    <span class="text-xs font-bold"
                        :class="{
                            'text-yellow-500': product.rank == 1,
                            'text-blue-500': product.rank == 2,
                            'text-amber-600': product.rank == 3,
                            'text-gray-600 dark:text-zinc-500': product.rank > 3
                        }"
                        x-text="'#' + product.rank"></span>
                </td>
                <td class="py-3">
                    <div class="flex items-center gap-3">
                        <img :src="product.image ??
                            'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                            class="w-12 h-12 rounded-sm object-cover bg-gray-100 dark:bg-zinc-800 shrink-0">
                        <div class="flex flex-col text-start">
                            <span class="font-medium text-gray-800 dark:text-zinc-200 truncate max-w-[250px]"
                                x-text="product.name"></span>
                            <span class="text-xs text-gray-600 dark:text-zinc-400" x-text="product.category"></span>
                        </div>
                    </div>
                </td>
                <td class="text-gray-700 font-medium dark:text-zinc-200"
                    x-text="'$' + Number(product.price).toFixed(2)">
                </td>
                <td class="font-medium text-gray-700 dark:text-zinc-200"
                    x-text="'$' + Number(product.avg_sale_price).toFixed(2)"></td>
                <td class="font-medium text-gray-700 dark:text-zinc-200" x-text="product.sold"></td>
                <td>
                    <div class="flex items-center gap-2">
                        <p class="text-gray-700 dark:text-zinc-200 font-semibold"
                            x-text="'$' + Number(product.revenue).toFixed(2)"></p>
                        <div class="flex-1 h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0F6E8C] rounded-l-full" :style="`width: ${product.percent}%`"></div>
                        </div>
                        <span class="text-[10px] text-gray-400 shrink-0" x-text="product.percent + '%'"></span>
                    </div>
                </td>
            </x-table>
        </div>

        {{-- Categories Table (Redesigned) --}}
        <div x-show="tab === 'categories'" style="display: none;" class="overflow-x-auto">
            <x-table headers="headers.topCategories" rows="topCategories" as="category">
                <td >
                    <span class="text-xs font-bold"
                        :class="{
                            'text-yellow-500': category.rank == 1,
                            'text-blue-500': category.rank == 2,
                            'text-amber-600': category.rank == 3,
                            'text-gray-600 dark:text-zinc-500': category.rank > 3
                        }"
                        x-text="'#' + category.rank"></span>
                </td>
                <td class="py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-sm bg-[#0F6E8C]/5 flex items-center justify-center shrink-0"
                            x-html="category.svg"></div>
                        <div class="flex flex-col text-start">
                            <span class="font-medium text-gray-800 dark:text-zinc-200 truncate max-w-[250px]"
                                x-text="category.name"></span>
                            <span class="text-[12px] text-gray-600 dark:text-zinc-400" x-text="category.code"></span>
                        </div>
                    </div>
                </td>
                <td class="font-medium text-gray-700 dark:text-zinc-200"
                    x-text="category.products_count ?? category.products"></td>
                <td class="font-medium text-gray-700 dark:text-zinc-200" x-text="category.sold"></td>
                <td class="text-gray-700 font-medium dark:text-zinc-200"
                    x-text="'$' + Number(category.avg_sale_price ?? 0).toFixed(2)"></td>
                <td class="text-gray-700 font-medium dark:text-zinc-200"
                    x-text="'$' + Number(category.avg_order_value ?? 0).toFixed(2)"></td>
                <td>
                    <div class="flex items-center gap-2">
                        <p class="text-gray-700 dark:text-zinc-200 font-semibold"
                            x-text="'$' + Number(category.revenue).toFixed(2)"></p>
                        <div class="flex-1 h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                            <div class="h-full bg-[#0F6E8C] rounded-l-full" :style="`width: ${category.percent}%`">
                            </div>
                        </div>
                        <span class="text-[10px] text-gray-400 shrink-0" x-text="category.percent + '%'"></span>
                    </div>
                </td>
            </x-table>
        </div>

    </div>
</div>
