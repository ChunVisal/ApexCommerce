<div x-show="customerOpen" x-cloak class="fixed inset-0 z-[60]" style="display: none;">
    <div x-show="customerOpen" @click="customerOpen = false" class="absolute inset-0 bg-black/50"></div>

    <div x-show="customerOpen" @click.stop x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-zinc-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-zinc-100"
                x-text="selectedCustomer?.id ? 'Edit Customer' : 'New Customer'"></h3>
            <button @click="customerOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Name *</label>
                <input type="text" x-model="customerForm.name" placeholder="Customer name"
                    class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Phone *</label>
                <input type="text" x-model="customerForm.phone" placeholder="Phone number"
                    class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Email (optional)</label>
                <input type="email" x-model="customerForm.email" placeholder="Email"
                    class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">
            </div>
        </div>

        <div class="px-5 py-4 border-t border-gray-200 dark:border-zinc-800 flex gap-3">
            <button @click="customerOpen = false"
                class="flex-1 py-2 text-xs border rounded-lg text-gray-600 dark:text-zinc-300">
                Cancel
            </button>
            <button @click="saveCustomer()" :disabled="saving || !customerForm.name || !customerForm.phone"
                class="flex-[2] py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-lg hover:bg-[#0c5972] disabled:opacity-50">
                <i x-show="saving" class="fa-solid fa-spinner fa-spin mr-1"></i>
                <span x-text="selectedCustomer?.id ? 'Update' : 'Save'"></span>
            </button>
        </div>
    </div>
</div>
