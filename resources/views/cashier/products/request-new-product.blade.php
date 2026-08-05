<div x-show="requestNewProduct" x-cloak class="fixed inset-0 z-50 flex justify-end" style="display: none;">

    {{-- Backdrop Overlay (Fades In/Out) --}}
    <div @click="requestNewProduct = false" x-show="requestNewProduct"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-zinc-950/40 backdrop-blur-[2px]"></div>

    {{-- Premium Panel Container (Slides Right to Left) --}}
    <div @click.stop x-show="requestNewProduct" x-transition:enter="transition transform ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform ease-in duration-400" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="relative h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-2xl flex flex-col border-l border-gray-100 dark:border-zinc-800/80">

        {{-- Panel Header Segment --}}
        <div
            class="flex flex-col gap-1 px-5 py-4 border-b border-gray-100 dark:border-zinc-800/60 bg-gray-50/30 dark:bg-zinc-900/40">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-zinc-100">
                        Request Products
                    </h3>
                    <p class="text-[12px] text-gray-400 dark:text-zinc-400 ">
                        Make a request new product to admin
                    </p>
                </div>
                <button @click="requestNewProduct = false"
                    class="w-7 h-7 inline-flex items-center justify-center text-gray-400 hover:text-gray-900 dark:hover:text-zinc-100 bg-gray-100 dark:bg-zinc-800/60 rounded-md transition-colors">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Dynamic Items Container Section --}}
        <div class="flex-1 tab-container overflow-y-auto px-5 py-4 space-y-4">
            <template x-for="(item, index) in newProductList" :key="index">
                <div
                    class="bg-gray-50/60 dark:bg-zinc-950/30 border border-gray-200/50 dark:border-zinc-800/50 rounded-md p-3.5 space-y-3 relative group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500"
                            x-text="'Item #' + (index + 1)"></span>
                        <button @click="newProductList.splice(index, 1)"
                            class="text-red-500 dark:text-red-400/80 hover:text-red-700 dark:hover:text-red-400 text-xs font-bold uppercase tracking-wider transition-colors inline-flex items-center gap-0.5">
                            <x-heroicon-s-trash class="w-3.5 h-3.5" />
                            <span>Remove</span>
                        </button>
                    </div>

                    {{-- Form Component Controls --}}
                    <div class="space-y-2">
                        {{-- Option 1: Select existing product --}}
                        <div class="relative" x-data="{ searchTerm: '', open: false }">
                            <label class="block text-xs font-medium text-gray-500 dark:text-zinc-400 mb-1">Select
                                Existing Product</label>

                            {{-- Search Input --}}
                            <div @click="open = !open"
                                class="w-full text-xs border border-gray-200 dark:border-zinc-800 rounded-md px-3 py-2 bg-white dark:bg-zinc-950 cursor-pointer flex items-center justify-between text-gray-900 dark:text-zinc-100">
                                <span x-text="selectedProductName || 'Choose product...'"
                                    :class="!item.name && 'text-gray-400 dark:text-zinc-500'"></span>
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Dropdown --}}
                            <div x-show="open" @click.outside="open = false" x-cloak
                                class="absolute z-20 w-full mt-1 bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                <input type="text" x-model="searchTerm" placeholder="Search product..."
                                    class="sticky top-0 w-full text-xs border-b border-gray-200 dark:border-zinc-800 px-3 py-2 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none">

                                <template
                                    x-for="product in allProducts.filter(p => !searchTerm || p.name.toLowerCase().includes(searchTerm.toLowerCase())).slice(0, 20)"
                                    :key="product.id">
                                    <div @click="item.product_id = product.id; item.name = product.name; open = false"
                                        class="px-3 py-1.5 text-xs text-gray-900 dark:text-zinc-100 hover:bg-gray-100 dark:hover:bg-zinc-800 cursor-pointer">
                                        <span x-text="product.name"></span>
                                        
                                        <span class="text-gray-400 dark:text-zinc-500 ml-2"
                                            x-text="'(' + product.stock_quantity + ')'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Minimalist Divider --}}
                        <div class="relative text-center my-3">
                            <span
                                class="bg-white dark:bg-zinc-900 group-hover:bg-[#FCFCFC] dark:group-hover:bg-[#111113] transition-colors px-2 text-[10px] font-bold tracking-widest text-gray-400 dark:text-zinc-600 relative z-10">OR</span>
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200/60 dark:border-zinc-800/80"></div>
                            </div>
                        </div>

                        {{-- Option 2: Manual product name --}}
                        <div>
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500 mb-1.5">Or
                                Type Product Name</label>
                            <input type="text" x-model="item.name" placeholder="e.g. RTX 5090"
                                @input="item.product_id = ''"
                                class="w-full text-xs font-semibold border border-gray-400 dark:border-zinc-800/80 rounded-md px-3 py-2 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-650 focus:outline-none focus:border-[#0F6E8C] dark:focus:border-[#1389af] transition-colors">
                        </div>

                        {{-- Quantity and Notes Fields --}}
                        <div class="flex gap-2">
                            <input type="number" x-model="item.quantity" placeholder="Qty" min="1"
                                class="w-20 text-xs font-semibold text-center border border-gray-400 dark:border-zinc-800/80 rounded-md px-2 py-2 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-[#0F6E8C] dark:focus:border-[#1389af] transition-colors [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <input type="text" x-model="item.note" placeholder="Note (optional)"
                                class="flex-1 text-xs font-semibold border border-gray-400 dark:border-zinc-800/80 rounded-md px-3 py-2 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-650 focus:outline-none focus:border-[#0F6E8C] dark:focus:border-[#1389af] transition-colors">
                        </div>
                    </div>
                </div>
            </template>

            {{-- Dynamic Empty State within panel list --}}
            <template x-if="newProductList.length === 0">
                <div
                    class="text-center py-8 border border-dashed border-gray-200 dark:border-zinc-800 rounded-md bg-gray-50/30 dark:bg-zinc-900/10">
                    <p class="text-[11px] text-gray-400 dark:text-zinc-500 font-medium">No request lines appended yet.
                    </p>
                </div>
            </template>

            {{-- Add Item Action controller Trigger --}}
            <button @click="addNewProductItem()"
                class="w-full py-2.5 text-[11px] font-bold uppercase tracking-wider text-[#0F6E8C] dark:text-[#1389af] border border-dashed border-[#0F6E8C]/30 dark:border-[#1389af]/40 rounded-md hover:bg-[#0F6E8C]/5 dark:hover:bg-[#1389af]/5 transition-colors flex items-center justify-center gap-1.5">
                <x-heroicon-s-plus-circle class="w-4 h-4" />
                <span>Add Product Item</span>
            </button>
        </div>

        {{-- Bottom Panel Form Footer Commit Actions --}}
        <div
            class="px-5 py-4 border-t border-gray-100 dark:border-zinc-800/60 flex gap-3 bg-gray-50/30 dark:bg-zinc-900/40">
            <button @click="requestNewProduct = false"
                class="flex-1 py-2 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100 border border-gray-400 dark:border-zinc-800/80 rounded-md transition-colors">
                Cancel
            </button>
            <button @click="submitNewProductRequest()" :disabled="newProductList.length === 0"
                class="flex-[2] py-2 text-[11px] font-bold uppercase tracking-wider text-white bg-[#0F6E8C] hover:bg-cyan-700 dark:bg-[#1389af] dark:hover:bg-cyan-600 rounded-md disabled:opacity-40 disabled:pointer-events-none transition-colors flex items-center justify-center gap-1">
                <span>Send Requests</span>
                <span class="bg-black/10 dark:bg-white/10 px-1.5 py-0.5 rounded text-[10px]"
                    x-text="newProductList.length"></span>
            </button>
        </div>
    </div>
</div>
