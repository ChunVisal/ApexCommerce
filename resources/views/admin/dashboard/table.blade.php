{{-- resources/views/admin/partials/dashboard/top-tables.blade.php --}}
<div class="mt-4 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm p-5 border border-gray-200 dark:border-zinc-800/60"
    x-data="{ tab: 'products' }">
    {{-- Tab Header --}}
    <div class="flex flex-wrap items-center justify-between pb-3 border-b border-gray-200 dark:border-zinc-800">
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

    {{-- Products Table --}}
    <div x-show="tab === 'products'" class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-100 dark:border-zinc-800">
                    <th class="py-3 pl-4 pr-2 font-medium w-10">
                        <span
                            class="bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 px-2 py-0.5 rounded text-[11px]">No</span>
                    </th>
                    <th class="py-3 px-2 font-medium">Product</th>
                    <th class="py-3 px-2 font-medium text-center">Price</th>
                    <th class="py-3 px-2 font-medium text-center">Average Sale</th>
                    <th class="py-3 px-2 font-medium text-left">Sold</th>
                    <th class="py-3 font-medium text-right">Revenue & Performance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/50">
                @foreach ($topProducts as $product)
                    <tr class=" hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pl-4 pr-2">
                            <span
                                class="text-xs font-bold {{ $product['rank'] == 1 ? 'text-yellow-500' : ($product['rank'] == 2 ? 'text-blue-500' : ($product['rank'] == 3 ? 'text-amber-600' : 'text-gray-600 dark:text-zinc-500')) }}">
                                #{{ $product['rank'] }}
                            </span>
                        </td>
                        <td class="py-3 px-2">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product['image'] ?? 'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png' }}"
                                    class="w-12 h-12 rounded-sm object-cover bg-gray-100 dark:bg-zinc-800 shrink-0">
                                <div class="flex flex-col">
                                    <span
                                        class="font-medium text-gray-800 dark:text-zinc-200 truncate max-w-[250px]">{{ $product['name'] }}</span>
                                    <span
                                        class="text-xs text-gray-600 dark:text-zinc-400">{{ $product['category'] }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-2 text-center text-gray-700 font-medium dark:text-zinc-200">
                            ${{ number_format($product['price'], 2) }}</td>
                        <td class="py-3 px-2 text-center font-medium text-gray-700 dark:text-zinc-200">
                            ${{ number_format($product['avg_sale_price'], 2) }}</td>
                        <td class="py-3 px-2 text-left font-medium text-gray-700 dark:text-zinc-200">
                            {{ $product['sold'] }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-2">
                                <p class="text-gray-700 dark:text-zinc-200 font-semibold">
                                    ${{ number_format($product['revenue'], 2) }}</p>
                                <div class="flex-1 h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0F6E8C] rounded-l-full"
                                        style="width: {{ $product['percent'] }}%"></div>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">{{ $product['percent'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Categories Table (Redesigned) --}}
    <div x-show="tab === 'categories'" style="display: none;" class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-100 dark:border-zinc-800">
                    <th class="py-3 pl-4 pr-2 font-medium w-10">
                        <span
                            class="bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 px-2 py-0.5 rounded text-[11px]">No</span>
                    </th>
                    <th class="py-3 px-2 font-medium">Category</th>
                    <th class="py-3 px-2 font-medium text-center">Products Count</th>
                    <th class="py-3 px-2 font-medium text-center">Sold Items</th>
                    <th class="py-3 px-2 font-medium text-center">Average Sale</th>
                    <th class="py-3 px-2 font-medium text-center">Avg Order Value</th>
                    <th class="py-3 font-medium text-right">Revenue & Performance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-zinc-800/50">
                @foreach ($topCategories as $category)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        {{-- Clean Rank System Matching Product Grid --}}
                        <td class="py-3 pl-4 pr-2">
                            <span
                                class="text-xs font-bold {{ $category['rank'] == 1 ? 'text-yellow-500' : ($category['rank'] == 2 ? 'text-blue-500' : ($category['rank'] == 3 ? 'text-amber-600' : 'text-gray-600 dark:text-zinc-500')) }}">
                                #{{ $category['rank'] }}
                            </span>
                        </td>
                        {{-- Category Name and Visual Wrapper --}}
                        <td class="py-3 px-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-sm bg-[#0F6E8C]/5 dark:bg-[#0F6E8C]/5 flex items-center justify-center shrink-0">
                                    {!! $category['svg'] !!}
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="font-medium text-gray-800 dark:text-zinc-200 truncate max-w-[250px]">{{ $category['name'] }}</span>
                                    <span
                                        class="text-[12px] text-gray-600 dark:text-zinc-400">{{ $category['code'] }}</span>
                                </div>
                            </div>
                        </td>
                        {{-- Product Count --}}
                        <td class="py-3 px-2 text-center font-medium text-gray-700 dark:text-zinc-200">
                            {{ $category['products_count'] ?? $category['products'] }}
                        </td>
                        {{-- Sold items --}}
                        <td class="py-3 px-2 text-center font-medium text-gray-700 dark:text-zinc-200">
                            {{ $category['sold'] }}
                        </td>

                        <td class="py-3 px-2 text-center text-gray-700 font-medium dark:text-zinc-200">
                            ${{ number_format($category['avg_sale_price'] ?? 0, 2) }}
                        </td>
                        {{-- Average Order --}}
                        <td class="py-3 px-2 text-center text-gray-700 font-medium dark:text-zinc-200">
                            ${{ number_format($category['avg_order_value'] ?? 0, 2) }}
                        </td>
                        {{-- Dynamic Revenue Bar --}}
                        <td class="py-3">
                            <div class="flex items-center gap-2">
                                <p class="text-gray-700 dark:text-zinc-200 font-semibold">
                                    ${{ number_format($category['revenue'], 2) }}
                                </p>
                                <div class="flex-1 h-2 bg-gray-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#0F6E8C] rounded-l-full"
                                        style="width: {{ $category['percent'] }}%"></div>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">{{ $category['percent'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
