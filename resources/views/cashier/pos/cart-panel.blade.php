<div
    class="bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-800 h-full flex flex-col max-h-[calc(100vh-100px)]">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Current Order</h3>
        <button @click="if(confirm('Clear all items from cart?')) cartItems = []" x-show="cartItems.length > 0"
            class="flex  items-center text-xs font-semibold text-red-500 hover:text-red-600 transition">
            <i class="fa-solid fa-trash-can mr-1"></i>
            Clear All
        </button>
    </div>

    {{-- Cart Items - Scrollable --}}
    <div class="flex-1 overflow-y-auto tab-container p-3 space-y-2">
        <template x-for="(item, index) in cartItems" :key="index">
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-zinc-800/80 rounded-sm pl-2 py-2 pr-4 group">

                {{-- Product Image --}}
                <img :src="item.image ||
                    'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                    class="w-[60px] h-[60px] rounded-xs object-cover shrink-0 bg-gray-200 dark:bg-zinc-700">

                {{-- Name + Controls --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800 dark:text-zinc-200 truncate" x-text="item.name"></p>
                    <p class="text-xs text-gray-600 dark:text-zinc-400">$<span
                            x-text="(item.price * item.qty).toFixed(2)"></span>
                        <span class="text-[11px] text-gray-500" x-text="(item.base_unit || '')"></span>
                    </p>

                    {{-- Quantity Controls --}}
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <button @click="decreaseQty(index)"
                            class="w-6 h-6 bg-red-100 dark:bg-red-900/70 text-red-300 rounded flex items-center justify-center text-xs hover:bg-red-200 transition">
                            <i class="fa-solid fa-minus text-[10px]"></i>
                        </button>
                        <span class="text-xs font-bold text-gray-800 dark:text-zinc-100 min-w-[20px] text-center"
                            x-text="item.qty"></span>
                        <button @click="increaseQty(index)"
                            class="w-6 h-6 bg-green-100 dark:bg-green-900/70 text-green-300 rounded flex items-center justify-center text-xs hover:bg-green-200 transition">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                        </button>
                    </div>
                </div>

                {{-- Unit Price + Delete --}}
                <button @click="removeItem(index)" class="text-gray-300 hover:text-red-400 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </template>

        {{-- Empty cart --}}
        <div x-show="cartItems.length === 0" class="text-center py-12">
            <div class="text-gray-300 dark:text-zinc-600 mb-2">
                <i class="fa-solid fa-cart-shopping text-3xl"></i>
            </div>
            <p class="text-xs text-gray-400">Cart is empty</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Add products to get started</p>
        </div>
    </div>

    {{-- Totals & Checkout - Sticky bottom --}}
    <div
        class="border-t border-gray-200 dark:border-zinc-800 p-4 space-y-2 shrink-0 bg-white dark:bg-zinc-900 rounded-b-lg">
        <div class="flex text-gray-500 dark:text-zinc-300 justify-between text-xs">
            <span class="">Subtotal</span>
            <span class="font-medium" x-text="'$' + subtotal.toFixed(2)"></span>
        </div>
        <div class="flex text-gray-500 dark:text-zinc-300 justify-between text-xs">
            <span class="">Tax ({{ App\Models\Setting::get('tax_rate', '10') }}%)</span>
            <span class="font-medium" x-text="'$' + tax.toFixed(2)"></span>
        </div>
        <div class="flex justify-between text-sm font-bold border-t border-gray-200 dark:border-zinc-700 pt-2">
            <span class="text-gray-600 dark:text-zinc-200">Total</span>
            <span class="text-[#0F6E8C]" x-text="'$' + total.toFixed(2)"></span>
        </div>

        <div class="flex gap-2 items-center">
            {{-- Hold Cart Button (High Contrast UI/UX) --}}
            <button @click="holdCart()" :disabled="cartItems.length === 0"
                class="flex-1 py-2.5 px-4 text-sm font-semibold text-white bg-amber-400 hover:bg-amber-500  dark:text-white dark:bg-amber-600 dark:hover:bg-amber-500 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <i class="fa-solid fa-pause mr-1"></i> Hold Cart
            </button>
            <button @click="openCheckout()" :disabled="cartItems.length === 0"
                class="flex-1 py-2.5 text-sm font-semibold text-white bg-[#0F6E8C] rounded-lg hover:bg-[#0c5972] disabled:opacity-50 disabled:cursor-not-allowed transition">
                Checkout
            </button>
        </div>
    </div>
</div>
