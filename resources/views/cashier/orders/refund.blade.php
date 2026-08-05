<div x-show="refundOpen" x-cloak class="fixed inset-0 z-50 flex justify-end" style="display: none;">
    {{-- Backdrop --}}
    <div @click="refundOpen = false" x-show="refundOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-zinc-950/60 backdrop-blur-xs"></div>

    {{-- Slide-over Drawer --}}
    <div @click.stop x-show="refundOpen" x-transition:enter="transition ease-out duration-250 transform"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="relative z-10 w-full max-w-md bg-white dark:bg-zinc-900 border-l border-gray-200 dark:border-zinc-800 shadow-2xl flex flex-col h-full">

        {{-- Drawer Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-150 dark:border-zinc-800/80">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                    <h3 class="text-sm font-bold uppercase text-gray-900 dark:text-zinc-100">Refund Order Process</h3>
                </div>
                <p class="ml-4 text-xs text-gray-500 dark:text-zinc-400 mt-1">Initiate a refund for the selected order below.
                </p>
            </div>
            <button @click="refundOpen = false"
                class="p-1 rounded-md text-gray-400 hover:text-gray-600 dark:text-zinc-500 dark:hover:text-zinc-300 transition-colors">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Content Area --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

            {{-- Summary Card --}}
            <div
                class="bg-gray-50 dark:bg-zinc-800/50 border border-gray-200/80 dark:border-zinc-800 rounded-lg p-4 flex items-center justify-between">
                <div>
                    <span
                        class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500">Order
                        Reference</span>
                    <p class="text-sm font-bold text-gray-900 dark:text-zinc-100 mt-0.5">#<span
                            x-text="refundOrderNumber"></span></p>
                </div>
                <div class="text-right">
                    <span
                        class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500">Total
                        Amount</span>
                    <p class="text-sm font-extrabold text-rose-600 dark:text-rose-400 mt-0.5">$<span
                            x-text="refundTotal"></span></p>
                </div>
            </div>

            {{-- Select Reason --}}
            <div>
                <label
                    class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 mb-1.5">Reason
                    for Refund</label>
                <div class="relative">
                    <select x-model="refundReason"
                        class="w-full text-xs font-medium border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2.5 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-rose-500 dark:focus:border-rose-500 transition-colors appearance-none">
                        <option value="" class="text-gray-400">Select reason...</option>
                        <option value="Customer return">Customer return</option>
                        <option value="Damaged product">Damaged product</option>
                        <option value="Wrong item">Wrong item delivered</option>
                        <option value="Price error">Price error</option>
                        <option value="Other">Other</option>
                    </select>
                    <x-heroicon-o-chevron-down
                        class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
                </div>
            </div>

            {{-- Toggle Box --}}
            <div class="p-3.5 border border-gray-200 dark:border-zinc-800 rounded-lg bg-white dark:bg-zinc-900 flex items-start gap-3 cursor-pointer"
                @click="restockItems = !restockItems">
                <input type="checkbox" x-model="restockItems" id="restockCheck" @click.stop
                    class="mt-0.5 rounded border-gray-300 dark:border-zinc-700 text-rose-600 focus:ring-rose-500/20 dark:bg-zinc-800">
                <label for="restockCheck" class="text-xs select-none cursor-pointer">
                    <span class="font-bold block text-gray-900 dark:text-zinc-200">Restock inventory items</span>
                    <span class="text-gray-400 dark:text-zinc-500 text-[11px] block mt-0.5">Automatically add these
                        quantities back to active stock.</span>
                </label>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div
            class="px-6 py-4 border-t border-gray-150 dark:border-zinc-800/80 bg-gray-50/50 dark:bg-zinc-900/50 flex gap-3">
            <button @click="refundOpen = false"
                class="flex-1 py-2 text-xs font-semibold text-gray-700 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors">
                Cancel
            </button>
            <button @click="processRefund()" :disabled="!refundReason"
                class="flex-[2] py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-md disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm">
                Confirm Refund
            </button>
        </div>
    </div>
</div>
