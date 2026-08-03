<div x-show="requestOpen" x-cloak class="fixed inset-0 z-50 animate-fade-in" style="display: none;">
    <!-- Premium Backdrop Glassmorphism Overlay -->
    <div @click="requestOpen = false" x-show="requestOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/30 dark:bg-zinc-950/60 backdrop-blur-[2px]">
    </div>

    <!-- Minimal Luxury Slide-over Surface Panel -->
    <div @click.stop x-show="requestOpen" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-gray-50 dark:bg-zinc-900 shadow-2xl dark:shadow-zinc-950/80 border-l border-gray-100 dark:border-zinc-800/80 flex flex-col">

        {{-- Slide-over Header Component --}}
        <div
            class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-zinc-800/60 bg-gray-50/50 dark:bg-zinc-900/30">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-zinc-100 uppercase">Request Restock
                </h3>
                <p class="text-[12px] text-gray-400 dark:text-zinc-500 mt-0.5">Procure inventory updates directly</p>
            </div>
            <button @click="requestOpen = false"
                class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300 transition-colors p-1 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800/60">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Search Input Section Block --}}
        <div class="px-6 pt-5">
            <div class="relative w-full">
                <i
                    class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
                <input type="text" x-model="requestSearch" placeholder="Search low stock product..."
                    class="w-full text-xs pl-9 pr-4 py-2 bg-white dark:bg-zinc-950/40 text-gray-800 dark:text-zinc-200 border border-gray-200 dark:border-zinc-800 rounded-md focus:outline-none focus:border-[#0F6E8C] placeholder-gray-500 dark:placeholder-zinc-400 transition-colors">
            </div>
        </div>

        {{-- Premium Minimalist Card List Feed Wrapper --}}
        <div class="flex-1 overflow-y-auto px-6 py-1     space-y-2.5">
            <template x-for="product in filteredRequestProducts" :key="product.id">
                <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-800/50 rounded-md p-3 transition-all duration-200"
                    :class="requestForm.product_id === product.id ?
                        'bg-zinc-50 dark:bg-zinc-900  border-[#0F6E8C]/30 dark:border-[#0F6E8C]/30 shadow-sm ring-1 ring-[#0F6E8C]/10' :
                        'bg-white dark:bg-zinc-900 hover:border-gray-300 dark:hover:border-zinc-800'">

                    <div class="flex items-start justify-between gap-1">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-[55px] h-[55px] rounded-sm bg-gray-200/80 dark:bg-zinc-850 border border-gray-100 dark:border-zinc-800 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                <template x-if="product.image">
                                    <img :src="product.image" :alt="product.name"
                                        class="w-full h-full object-cover">
                                </template>
                                <template x-if="!product.image">
                                    <img src="https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png"
                                        alt="Placeholder" class="w-full h-full object-cover">
                                </template>
                            </div>
                            <div class="s
                            pace-y-1">
                                <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 leading-tight"
                                    x-text="product.name"></p>
                                {{-- Precision Context Stock Alert Badges --}}
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold border"
                                    :class="product.remaining == 0 ?
                                        'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/40 dark:text-red-200 dark:border-red-800/60' :
                                        'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-800/60'">

                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                        :class="product.remaining == 0 ? 'bg-red-500 dark:bg-red-400' :
                                            'bg-amber-500 dark:bg-amber-400'"></span>

                                    <span
                                        x-text="product.remaining == 0 ? 'Out of stock' : product.remaining + ' units remaining'"></span>
                                </div>
                                <!-- Add Note To Me Input When Selected -->
                                <div x-show="requestForm.product_id === product.id" class="mt-2">
                                    <input type="text" x-model="requestForm.note" placeholder="Note to me (optional)"
                                        class="w-full text-xs px-2 py-1.5 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 border border-gray-200 dark:border-zinc-700 rounded transition-colors focus:outline-none focus:border-[#0F6E8C] placeholder-gray-400 dark:placeholder-zinc-500">
                                </div>
                            </div>
                        </div>

                        {{-- Selection Action Switches Component --}}
                        <div class="flex items-center pt-0.5">
                            <button type="button"
                                @click="requestForm.product_id = product.id; requestForm.product_name = product.name"
                                x-show="requestForm.product_id !== product.id"
                                class="text-[11px] font-bold text-[#0F6E8C] dark:text-[#1389af] hover:text-[#0c5972] dark:hover:text-[#18abdb] transition-colors uppercase tracking-wider">
                                Select
                            </button>
                            <button type="button"
                                @click="requestForm.product_id = ''; requestForm.product_name = ''; requestForm.quantity = 1; requestForm.note = ''"
                                x-show="requestForm.product_id === product.id"
                                class="text-gray-400 dark:text-zinc-500 hover:text-red-500 dark:hover:text-red-400 transition-colors p-1 rounded">
                                <i class="fa-solid fa-circle-xmark text-sm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Dynamic Quantity Entry Slide Mechanism Component --}}
                    <div x-show="requestForm.product_id === product.id"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="flex items-center gap-2 mt-3.5 pt-3 border-t border-gray-200/60 dark:border-zinc-800/60">

                        <div class="relative flex-1 max-w-[90px]">
                            <input type="number" x-model="requestForm.quantity" min="1" placeholder="Qty"
                                class="w-full text-xs font-semibold text-center border border-gray-300 dark:border-zinc-700 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-800 text-gray-800 dark:text-zinc-200 focus:outline-none focus:border-[#0F6E8C]">
                        </div>

                        <button @click="submitRequest()" :disabled="!requestForm.quantity"
                            class="flex-1 h-[29px] text-xs font-bold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition-colors disabled:opacity-40 disabled:hover:bg-[#0F6E8C] whitespace-nowrap uppercase tracking-wider">
                            Send Request
                        </button>
                    </div>
                </div>
            </template>

            {{-- Empty Data Placeholder Block State --}}
            <div x-show="filteredRequestProducts.length === 0"
                class="text-center py-16 rounded-md border border-dashed border-gray-200 dark:border-zinc-800 bg-gray-100 dark:bg-zinc-900 flex flex-col items-center justify-center">

                {{-- Dynamic circular icon layout --}}
                <div
                    class="w-14 h-14 rounded-full bg-gray-100 dark:bg-zinc-800/40 border border-gray-200/50 dark:border-zinc-800/60 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-box-open text-gray-400 dark:text-zinc-500 text-md"></i>
                </div>

                {{-- Typography hierarchy --}}
                <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase">
                    All Stock Levels Stable
                </p>
                <p class="text-[11px] text-gray-400 dark:text-zinc-505 mt-1 max-w-[220px] leading-relaxed">
                    No low stock products require current attention.
                </p>
            </div>
        </div>
    </div>
</div>
