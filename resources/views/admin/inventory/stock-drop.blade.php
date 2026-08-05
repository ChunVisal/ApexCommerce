<div x-show="stockDropOpen" x-cloak class="fixed inset-0 z-50" style="display: none;">

    <div @click.stop @click.outside="stockDropOpen = false" x-show="stockDropOpen"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-zinc-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-zinc-100">Drop Stock to Cashier</h3>
            <button @click="stockDropOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            {{-- Product (readonly) --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Product</label>
                <input type="text" x-model="dropForm.product_name" readonly
                    class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">
            </div>

            {{-- Current Stock --}}
            <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-3 flex justify-between">
                <span class="text-xs text-gray-500">Available Stock</span>
                <span class="text-sm font-bold text-gray-800 dark:text-zinc-200" x-text="dropForm.current_stock"></span>
            </div>

            {{-- Cashier Selection --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Select Cashier</label>
                
                <select x-model="dropForm.cashier_id"
                    class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">
                    <option value="">Choose cashier...</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}">{{ $cashier->name }}
                            ({{ $cashier->employee_id ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Quantity --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Quantity to Transfer</label>
                <input type="number" x-model="dropForm.quantity" min="1" :max="dropForm.current_stock"
                    class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">
                <p class="text-[11px] text-gray-400 mt-1" x-show="dropForm.cashier_id">
                    Current at cashier: <span x-text="getCashierStock()" class="font-medium"></span>
                </p>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-gray-200 dark:border-zinc-800 flex gap-3">
            <button @click="stockDropOpen = false"
                class="flex-1 py-2 text-xs border rounded-lg text-gray-600 dark:text-zinc-300">Cancel</button>
            <button @click="submitDrop()" :disabled="!dropForm.cashier_id || !dropForm.quantity"
                class="flex-[2] py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-lg hover:bg-[#0c5972] disabled:opacity-50">
                Transfer Stock
            </button>
        </div>
    </div>
    s
</div>
