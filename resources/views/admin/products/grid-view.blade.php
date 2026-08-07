{{-- Grid View --}}
<div x-show="viewMode === 'grid'">
    <div>
        {{-- Product Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 gap-4">
            <template x-for="product in filteredProducts" :key="product.id">
                <div :data-category="product.category?.name || ''" :data-status="product.status"
                    :data-stock="product.stock_quantity" :data-threshold="product.low_stock_threshold"
                    class="group rounded-md bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 flex flex-col shadow-xs hover:shadow-md transition-all overflow-hidden">

                    {{-- Image with Code Badge --}}
                    <div class="relative w-full h-48 overflow-hidden bg-gray-100 dark:bg-zinc-800">
                        <img :src="product.image ||
                            'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                            class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300" />

                        {{-- Product Code Badge --}}
                        <span
                            class="absolute top-0.5 right-1 bg-zinc-900/90 backdrop-blur-xs text-white text-[10px] px-2 py-0.5 rounded font-mono font-medium tracking-wide"
                            x-text="product.code">
                        </span>
                    </div>

                    {{-- Content Body --}}
                    <div class="p-3 flex flex-col flex-1 bg-gray-50/50 dark:bg-zinc-900/50">

                        {{-- Title & Category --}}
                        <div class="space-y-0.5">
                            <p class="text-xs font-semibold text-gray-900 dark:text-zinc-100 line-clamp-2 leading-snug"
                                x-text="product.name"></p>
                            <div class="flex items-center gap-1  font-medium">
                                <p class="text-[12px] text-gray-500 dark:text-zinc-400 truncate"
                                    x-text="product.category?.name || 'Unassigned'"></p>
                                <span class="ml-1 whitespace-nowrap text-[12px] text-gray-500 dark:text-zinc-400  "
                                    x-text="'(' + (product.stock_quantity ?? 0) + ' in stock)'"></span>
                            </div>
                        </div>


                        {{-- Price & Date --}}
                        <div class="mt-auto pt-2">
                            <p class="text-sm font-bold text-[#0F6E8C] dark:text-[#188cb3]"
                                x-text="'$' + Number(product.selling_price).toFixed(2)"></p>
                            <p class="text-[10px] text-gray-500 dark:text-zinc-500">
                                Created: <span class="font-medium"
                                    x-text="product.created_at ? new Date(product.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '-'"></span>
                            </p>
                        </div>

                        {{-- Footer: Stock & Actions --}}
                        <div
                            class="flex items-center justify-between mt-1 pt-1.5 border-t border-gray-200/60 dark:border-zinc-800">

                            {{-- Stock Status Badge --}}
                            <template x-if="product.stock_quantity <= 0">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-500 text-white">
                                    Out of stock
                                </span>
                            </template>
                            <template
                                x-if="product.stock_quantity > 0 && product.stock_quantity < product.low_stock_threshold">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-500 text-white"
                                    x-text="product.stock_quantity + ' Low stock'"></span>
                            </template>
                            <template x-if="product.stock_quantity >= product.low_stock_threshold">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-600 text-white"
                                    x-text="product.stock_quantity + ' In stock'"></span>
                            </template>

                            {{-- Action Controls --}}
                            <div class="flex items-center gap-1">
                                {{-- Edit Button --}}
                                <button @click="openEdit(product)" type="button"
                                    class="text-gray-400 dark:text-zinc-500 hover:text-[#0F6E8C] dark:hover:text-[#188cb3] transition-colors py-0.5"
                                    title="Edit Product">
                                    <x-heroicon-m-pencil-square class="w-4 h-4" />
                                </button>

                                {{-- Delete Button --}}
                                <button @click="deleteProduct(product.id, $el)" type="button"
                                    class="text-red-400 dark:text-red-500 hover:text-rose-600 transition-colors py-0.5"
                                    title="Delete Product">
                                    <x-heroicon-m-trash class="w-4 h-4" />
                                </button>

                                {{-- Bulk Action Checkbox --}}
                                <input type="checkbox"
                                    class="bulk-checkbox rounded border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 text-[#0F6E8C] focus:ring-0 cursor-pointer ml-1"
                                    :data-id="product.id" @change="updateBulkBar()">
                            </div>

                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
