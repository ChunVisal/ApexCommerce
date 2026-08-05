<!-- STOCK ADJUSTMENT SLIDE-OVER PANEL -->
<div x-show="open" x-cloak class="fixed inset-0 z-50" style="display: none;">
    <div x-show="open" x-transition.opacity @click="closePanel()"
        class="absolute inset-0 bg-gray-900/40 dark:bg-black/60"></div>

    <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col border-l border-gray-400 dark:border-zinc-800">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-400 dark:border-zinc-800">
            <h2 class="font-medium text-gray-800 tracking-tighter uppercase dark:text-zinc-100">Stock Adjustment</h2>
            <button @click="closePanel()"
                class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
            <div x-data="{ search: '', open: false }">
                <label
                    class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 mb-1">Product</label>

                {{-- Click to open --}}
                <div @click="open = !open"
                    class="w-full text-sm px-3 py-2 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-800 cursor-pointer flex items-center justify-between transition-colors">
                    <span
                        x-text="products.find(p => p.code === form.product_code) ? (products.find(p => p.code === form.product_code).name + ' (' + form.product_code + ')') : 'Select product'"
                        :class="!form.product_code && 'text-gray-500 dark:text-zinc-400'"></span>
                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 dark:text-zinc-400" />
                </div>

                {{-- Dropdown Menu --}}
                <div x-show="open" @click.outside="open = false" x-cloak
                    class="absolute z-20 w-[390px] tab-container overflow-x-hidden mt-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-md shadow-lg max-h-[450px] overflow-y-auto">

                    <input type="text" x-model="search" placeholder="Search product..."
                        class="sticky top-0 w-[390px] text-sm border-b border-gray-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-500 dark:placeholder-zinc-400 focus:outline-none">

                    @php
                        $hasProductMatch = false;
                    @endphp
                    @foreach ($products as $product)
                        <div x-show="!search || '{{ strtolower($product->name) }} {{ strtolower($product->code) }}'.includes(search.toLowerCase())"
                            @click="form.product_code = '{{ $product->code }}'; open = false"
                            class="px-3 w-[390px] py-2 text-sm text-gray-850 dark:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer">
                            {{ $product->name }} <span
                                class="text-gray-500 dark:text-zinc-500 text-xs">({{ $product->code }})</span>
                        </div>
                    @endforeach
                    {{-- Not found message --}}
                    <div x-show="search && !Array.from($el.parentNode.querySelectorAll('div[ x-show ]')).some(div => div.offsetParent !== null)"
                        class="px-3 py-4 text-center text-gray-400 dark:text-zinc-500 text-sm select-none">
                        No product found.
                    </div>
                </div>

            </div>

            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-2">Movement
                    Type</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="form.type = 'in'"
                        class="flex items-center justify-center gap-2 py-2 rounded-md text-xs font-semibold border transition"
                        :class="form.type === 'in' ?
                            'bg-green-50 dark:bg-green-950/40 border-green-300 dark:border-green-800 text-green-700 dark:text-green-400' :
                            'border-gray-300 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800'">
                        <x-heroicon-o-arrow-long-up class="w-3.5 h-3.5" />
                        Stock In
                    </button>
                    <button type="button" @click="form.type = 'out'"
                        class="flex items-center justify-center gap-2 py-2 rounded-md text-xs font-semibold border transition"
                        :class="form.type === 'out' ?
                            'bg-red-50 dark:bg-red-950/40 border-red-300 dark:border-red-800 text-red-700 dark:text-red-400' :
                            'border-gray-300 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800'">
                        <x-heroicon-o-arrow-long-down class="w-3.5 h-3.5" />
                        Stock Out
                    </button>

                </div>
            </div>

            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">Quantity</label>
                <input type="number" min="1" x-model.number="form.quantity" placeholder="0"
                    class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] placeholder-gray-400 dark:placeholder-zinc-500">
                <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1" x-show="currentStock !== null">
                    Current stock: <span x-text="currentStock"
                        class="font-medium text-gray-600 dark:text-zinc-300"></span>
                </p>

            </div>
            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">
                    Warning Number
                    <span class="text-gray-400 dark:text-zinc-500 font-normal">(show warning when stock drops to
                        this)</span>
                </label>
                <input type="number" min="0" x-model.number="form.low_stock_threshold" placeholder="5"
                    class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] placeholder-gray-400 dark:placeholder-zinc-500">
                <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1">
                    Current warning number: <span x-text="currentThreshold ?? '-'"
                        class="font-medium text-gray-600 dark:text-zinc-300"></span>
                </p>
            </div>

            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">Reason</label>
                <div class="relative">
                    <select x-model="form.reason" required
                        class="appearance-none w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md pl-3 pr-8 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                        <option value="">Select reason</option>
                        <option value="Restock">Restock</option>
                        <option value="Return">Customer Return</option>
                        <option value="Damaged">Damaged / Defective</option>
                        <option value="Correction">Stock Count Correction</option>
                        <option value="Other">Other</option>
                    </select>
                    <x-heroicon-o-chevron-down
                        class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-2">Status</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="form.status = 'active'"
                        class="py-2 rounded-md text-xs font-semibold border transition"
                        :class="form.status === 'active' ?
                            'bg-green-50 dark:bg-green-950/40 border-green-300 dark:border-green-800 text-green-700 dark:text-green-400' :
                            'border-gray-300 dark:border-zinc-700 text-gray-500 dark:text-zinc-400'">
                        Active
                    </button>
                    <button type="button" @click="form.status = 'inactive'"
                        class="py-2 rounded-md text-xs font-semibold border transition"
                        :class="form.status === 'inactive' ?
                            'bg-red-50 dark:bg-red-950/40 border-red-300 dark:border-red-800 text-red-700 dark:text-red-400' :
                            'border-gray-300 dark:border-zinc-700 text-gray-500 dark:text-zinc-400'">
                        Inactive
                    </button>
                </div>
            </div>
            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">Notes
                    (optional)</label>
                <textarea x-model="form.notes" rows="3" placeholder="Add any additional notes..."
                    class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] placeholder-gray-400 dark:placeholder-zinc-500"></textarea>
            </div>
        </form>

        <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-400 dark:border-zinc-800">
            <button @click="closePanel()" type="button"
                class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800">
                Cancel
            </button>
            <button @click="submitForm()" :disabled="submitting" type="button"
                class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] disabled:opacity-60">
                Save Adjustment
                <span x-text="submitting ? 'Saving...' : 'Save Adjustment'"></span>
            </button>
        </div>
    </div>
</div>
