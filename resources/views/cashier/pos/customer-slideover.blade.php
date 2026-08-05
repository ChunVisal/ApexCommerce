{{-- Customer Slideover --}}
<div x-show="customerOpen" x-cloak class="fixed inset-0 z-[60]" style="display: none;">
    <div x-show="customerOpen" @click="customerOpen = false" class="absolute inset-0 bg-black/50"></div>

    <div x-show="customerOpen" @click.stop x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-zinc-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-zinc-100">Customer Info</h3>
            <button @click="customerOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            {{-- Search --}}
            <div class="relative">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search Customer</label>
                <i class="fa-solid fa-search absolute left-3 top-[34px] text-gray-400 text-xs"></i>
                <input type="text" x-model="customerSearch" @input="searchCustomers()" @focus="showAllCustomers()"
                    placeholder="Name or phone..."
                    class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-lg pl-9 pr-3 py-2 bg-white dark:bg-zinc-800 text-gray-800 dark:text-zinc-200">

                {{-- Dropdown List --}}
                <div x-show="customerResults.length > 0"
                    class="tab-container absolute left-0 right-0 top-[66px] bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg z-10 max-h-[200px] overflow-y-auto">

                    {{-- Header with close --}}
                    <div
                        class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-zinc-700 sticky top-0 bg-white dark:bg-zinc-800">
                        <span class="text-[10px] text-gray-500">Search Results</span>
                        <button @click="customerResults = []; customerSearch = ''"
                            class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark text-[10px]"></i>
                        </button>
                    </div>

                    <template x-for="cust in customerResults" :key="cust.id">
                        <div @click="selectCustomer(cust)"
                            class="flex items-center justify-between px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-700 transition border-b border-gray-100 dark:border-zinc-700 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-zinc-200 truncate"
                                    x-text="cust.name"></p>
                                <p class="text-xs text-gray-400" x-text="cust.phone"></p>
                                <template x-if="cust.email">
                                    <p class="text-xs text-gray-400 break-all" x-text="cust.email"></p>
                                </template>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full ml-2 shrink-0"
                                :class="cust.segment === 'vip' ?
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                    cust.segment === 'regular' ?
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                    'bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-zinc-400'"
                                x-text="cust.segment"></span>
                        </div>
                    </template>

                </div>

                {{-- No results --}}
                <div x-show="customerSearch && customerResults.length === 0"
                    class="absolute left-0 right-0 top-[66px] bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg z-10 p-4 text-center">
                    <p class="text-xs text-gray-400">No customers found</p>
                </div>
            </div>

            {{-- Divider --}}
            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-zinc-700"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white dark:bg-zinc-900 px-2 text-xs text-gray-400">OR</span>
                </div>
            </div>

            {{-- New Customer Form --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">New Customer</h4>
                <div class="space-y-3">
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
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-gray-200 dark:border-zinc-800 flex gap-3">
            <button @click="customerOpen = false; selectedCustomer = null"
                class="flex-1 py-2 text-xs border rounded-lg text-gray-600 dark:text-zinc-300">
                Skip
            </button>
            <button @click="saveCustomer()" :disabled="!customerForm.name || !customerForm.phone"
                class="flex-[2] py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-lg hover:bg-[#0c5972] disabled:opacity-50">
                Save Customer
            </button>
        </div>
    </div>
</div>
