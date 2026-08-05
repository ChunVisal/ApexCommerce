{{-- Return Stock Slideover --}}
<div x-show="returnOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
    {{-- Backdrop with premium fade transition --}}
    <div @click="returnOpen = false" x-show="returnOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm">
    </div>

    {{-- Slideover Panel with fluid horizontal translate translation --}}
    <div @click.stop x-show="returnOpen"
        x-transition:enter="transition ease-out duration-350 cubic-bezier(0.16, 1, 0.3, 1)"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-250 cubic-bezier(0.16, 1, 0.3, 1)"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 border-l border-gray-100 dark:border-zinc-800 shadow-2xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800/80">
            <div>
                <h3 class="text-md font-bold text-gray-900 dark:text-zinc-100">Report
                    Broken Stock</h3>
                <p class="text-[12px] text-gray-500 dark:text-zinc-500 mt-0.5">Submit product issues or defect
                    details to operations</p>
            </div>
            <button @click="returnOpen = false"
                class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-50 dark:hover:bg-zinc-800/50 text-gray-500 dark:text-zinc-500 hover:text-gray-900 dark:hover:text-zinc-100 transition-colors">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>

        {{-- Scrollable content body --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
            {{-- Product field (Readonly Style) --}}
            <div>
                <label
                    class="block text-[12px] font-bold text-gray-500 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Product</label>
                <div class="relative">
                    <input type="text" x-model="returnForm.product_name" readonly
                        class="w-full text-xs font-semibold border border-gray-200 dark:border-zinc-800/80 rounded-md px-3 py-2 bg-gray-50 dark:bg-zinc-800/40 text-gray-700 dark:text-zinc-300 pointer-events-none">
                    <x-heroicon-s-lock-closed
                        class="w-3.5 h-3.5 absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-zinc-500/80" />
                </div>
            </div>

            {{-- Quantity input field --}}
            <div>
                <label
                    class="block text-[12px] font-bold text-gray-500 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Quantity
                    Lost</label>
                <input type="number" x-model="returnForm.quantity" min="1" :max="returnForm.maxQuantity"
                    class="w-full text-xs font-semibold border border-gray-250 dark:border-zinc-800/80 rounded-md px-3 py-2 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-red-500 dark:focus:border-red-500/80 transition-colors">
                <p class="text-[11px] text-gray-400 mt-1">
                    Available: <span x-text="returnForm.maxQuantity"></span> units
                </p>
            </div>

            {{-- Reason select field --}}
            <div>
                <label
                    class="block text-[12px] font-bold text-gray-500 dark:text-zinc-500 uppercase tracking-wider mb-1.5">Reason
                    of Dispute</label>
                <div class="relative">
                    <select x-model="returnForm.reason"
                        class="w-full text-xs font-semibold border border-gray-250 dark:border-zinc-800/80 rounded-md pl-3 pr-8 py-2 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-red-500 dark:focus:border-red-500/80 appearance-none transition-colors">
                        <option value="">Select reason</option>
                        <option value="Damaged">Damaged / Broken on arrival</option>
                        <option value="Defective">Defective / Not working</option>
                        <option value="Missing">Missing items</option>
                        <option value="Theft">Stolen / Theft</option>
                        <option value="Accident">Accident (dropped, spilled)</option>
                        <option value="Other">Other</option>
                    </select>
                    <x-heroicon-o-chevron-down
                        class="w-3.5 h-3.5 absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-zinc-500 pointer-events-none" />
                </div>
            </div>
        </div>

        {{-- Footers action controllers --}}
        <div
            class="px-6 py-5 border-t border-gray-100 dark:border-zinc-800/80 flex gap-3 bg-gray-50/50 dark:bg-zinc-900/40">
            <button @click="returnOpen = false"
                class="flex-1 py-2 text-xs font-semibold border border-gray-250 dark:border-zinc-800 rounded-md bg-white dark:bg-zinc-900 hover:bg-gray-50 dark:hover:bg-zinc-800/60 text-gray-700 dark:text-zinc-300 transition-colors">
                Cancel
            </button>
            <button @click="submitReturn()" :disabled="!returnForm.reason || !returnForm.quantity"
                class="flex-[2] py-2 text-xs  text-white bg-red-600 dark:bg-red-700 hover:bg-red-700 dark:hover:bg-red-800 rounded-md transition-colors disabled:opacity-50 disabled:pointer-events-none shadow-sm shadow-red-500/10">
                Confirm Report Loss
            </button>
        </div>
    </div>
</div>
