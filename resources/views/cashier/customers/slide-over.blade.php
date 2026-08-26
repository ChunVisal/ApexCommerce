<x-slide-over name="customerPanelOpen">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-zinc-800/60">
        <h3 class="text-sm font-bold text-gray-900 dark:text-zinc-100"
            x-text="editMode ? 'Edit Customer' : 'Add Customer'"></h3>
        <button @click="customerPanelOpen = false" class="text-gray-400 hover:text-gray-600">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-zinc-400 mb-1">Name</label>
            <input type="text" x-model="customerForm.name"
                placeholder="Full Name"
                class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-[#0F6E8C]">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-zinc-400 mb-1">Phone</label>
            <input type="text" x-model="customerForm.phone"
                placeholder="Phone Number"
                class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-[#0F6E8C]">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-zinc-400 mb-1">Email (optional)</label>
            <input type="email" x-model="customerForm.email"
                placeholder="Email Address"
                class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-[#0F6E8C]">
        </div>
    </div>

    <div class="px-5 py-4 border-t border-gray-100 dark:border-zinc-800/60 flex gap-2">
        <button @click="customerPanelOpen = false"
            class="flex-1 py-2 text-xs border border-gray-300 dark:border-zinc-700 rounded-md text-gray-600 dark:text-zinc-300">Cancel</button>
        <button @click="submitCustomer()" :disabled="!customerForm.name || !customerForm.phone"
            class="flex-[2] py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md disabled:opacity-40">
            <span x-text="editMode ? 'Save Changes' : 'Add Customer'"></span>
        </button>
    </div>
</x-slide-over>
