{{-- LEFT: Products (75%) --}}
<div class="flex flex-col h-full">
    <div class="flex items-center justify-between mb-4 gap-4">

        {{-- Title - Left --}}
        <div class="flex items-center gap-1 shrink-0">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-zinc-100">Point of sale</h2>
            <span class="text-sm text-gray-500 dark:text-zinc-500 pt-0.5">({{ $totalAllocated }} items available)</span>
        </div>

        {{-- Search + Barcode --}}
        <div class="flex items-center gap-3 flex-1 max-w-xl">
            <div class="relative flex-1">
                <x-heroicon-m-magnifying-glass
                    class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-zinc-400" />
                <input x-model="searchQuery" type="search" placeholder="Search products, categories, code..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-400 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100 rounded-full text-sm outline-none">
            </div>

            {{-- Held Carts Dropdown Section --}}
            <div x-show="heldCartsList.length > 0" class="relative" x-data="{ open: false }">

                {{-- Trigger Button --}}
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-full text-xs font-bold text-gray-800 dark:text-zinc-200 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-pause text-gray-600 dark:text-zinc-400 text-[11px]"></i>
                        <span>Held Orders</span>
                        <span
                            class="px-2.5 flex-1 py-1 text-[10px] font-bold text-gray-100 bg-gray-700 dark:bg-zinc-600 rounded-full"
                            x-text="heldCartsList.length"></span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] px-1 rounded-full text-gray-500 dark:text-zinc-400 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>

                {{-- Dropdown Menu Container --}}
                <div x-show="open" @click.outside="open = false" x-cloak
                    class="absolute right-0 mt-1 w-[380px] sm:w-[420px] bg-white dark:bg-zinc-700 rounded-lg shadow-xl z-30 overflow-hidden max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800">

                    <template x-for="cart in heldCartsList" :key="cart.id">
                        <div
                            class="group flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition-colors">

                            {{-- Left Info Block --}}
                            <div class="flex-1 min-w-0 pr-3">
                                {{-- Top Row: Customer Name & Item Badge --}}
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                        <span x-text="cart.note || 'Note to remember'"></span>
                                    </p>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-200 shrink-0"
                                        x-text="cart.items.length + ' items'"></span>
                                </div>

                                {{-- Timestamp --}}
                                <div
                                    class="flex items-center gap-1.5 mt-1 text-[12px] text-gray-400 dark:text-zinc-300">
                                    <i class="fa-regular fa-clock text-[9px]"></i>
                                    <span x-text="cart.createdAt"></span>
                                </div>
                            </div>

                            {{-- Right Actions Block --}}
                            <div class="flex items-center gap-2 shrink-0">
                                {{-- Note Edit Button --}}
                                <button
                                    @click="cart.note = prompt('Add note:', cart.note || ''); localStorage.setItem('heldCarts', JSON.stringify(heldCartsList))"
                                    title="Edit Note"
                                    class="p-2 text-gray-400 dark:text-zinc-300 hover:text-gray-700 dark:hover:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-md transition-colors">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>

                                {{-- Resume Button --}}
                                <button @click="resumeCart(cart); open = false"
                                    class="px-3 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/80 border border-blue-200 dark:border-blue-800/80 rounded-md transition-colors shadow-sm">
                                    Resume
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Cards - Horizontal Scroll --}}
    <div class="tab-container overflow-x-auto pb-2 shrink-0">
        <div class="flex gap-4">

            {{-- "All Products" Reset Card --}}
            <div @click="selectedCategory = 'all'"
                :class="selectedCategory === 'all' ? 'border-[#1063a2]/30 bg-blue-50/50 dark:bg-zinc-800' :
                    'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'"
                class="w-32 h-32 p-3 border flex-shrink-0 hover:shadow-md transition-all cursor-pointer relative overflow-hidden flex flex-col items-center justify-center">
                <img src='{{ asset('images/allmenu.png') }}' class="object-cover h-full" alt="">
                <p class="text-sm font-medium text-gray-800 dark:text-zinc-100 mt-1">All Items</p>
            </div>

            {{-- Loop Through DB Categories --}}
            <template x-for="category in categories" :key="category.id">
                <div @click="selectedCategory = category.id"
                    :class="selectedCategory === category.id ?
                        'border-[#1063a2]/30 bg-blue-50/50 dark:bg-zinc-800' :
                        'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'"
                    class="w-32 h-32 p-3 border flex-shrink-0 hover:shadow-md transition-all cursor-pointer relative overflow-hidden flex flex-col justify-between">

                    <div class="w-full flex items-center justify-center mt-2">
                        <div class="rounded-sm p-1 text-gray-700 dark:text-zinc-300" x-html="category.svg"></div>

                        <span
                            class="absolute top-2 right-2 w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-500 text-[11px] font-bold text-gray-700 dark:text-zinc-300"
                            x-text="category.products_count"></span>
                    </div>

                    <div class="items-center flex flex-col text-center">
                        <p class="text-xs font-medium truncate w-full text-gray-800 dark:text-zinc-100"
                            x-text="category.name"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Products Grid Mapping Frame --}}
    <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

        <template x-for="product in filteredProducts" :key="product.id">

            <div x-show="selectedCategory === 'all' || selectedCategory === product.category_id"
                class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 overflow-hidden hover:shadow-md transition-all relative group flex flex-col rounded-sm">

                {{-- Product Code Badge --}}
                <span
                    class="absolute top-1 right-1 text-[10px] font-mono text-gray-600 dark:text-zinc-100 bg-gray-100 dark:bg-zinc-600 px-1.5 py-0.5 rounded z-10"
                    x-text="product.code"></span>

                {{-- Image --}}
                <div class="w-full h-[160px] bg-gray-50 dark:bg-zinc-600 overflow-hidden">
                    <img :src="product.image ||
                        'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                        class="w-full h-full object-cover">
                </div>

                {{-- Content --}}
                <div class="p-3 flex flex-col flex-1">
                    <p class="text-sm font-medium text-gray-800 dark:text-zinc-100 line-clamp-2" x-text="product.name">
                    </p>
                    <p class="text-xs mb-1 font-semibold text-gray-600 dark:text-zinc-300"
                        x-text="product.category?.name"></p>
                    <span
                        class="w-fit mb-3 text-[10px] font-mono text-gray-600 dark:text-zinc-100 bg-gray-100 dark:bg-zinc-600 px-1.5 py-0.5 rounded"
                        x-text="product.code"></span>

                    {{-- UOM or Normal Price --}}
                    <div class="mt-auto pt-2 border-t border-gray-100 dark:border-zinc-800">
                        {{-- UOM Select --}}
                        <div x-show="product.uom_list && product.uom_list.length > 0">
                            <select
                                class="w-full text-xs font-medium border rounded px-2 py-1.5 mb-1 bg-gray-200/30 text-gray-900 dark:bg-zinc-800 dark:text-zinc-100 border-gray-200 dark:border-zinc-700"
                                @change="
                                product._uomPrice = $event.target.selectedOptions[0].dataset.price;
                                product._uomStock = $event.target.selectedOptions[0].dataset.stock;
                                product._uomName = $event.target.selectedOptions[0].text.split(' - ')[0]; 
                                product._uomId = $event.target.value !== 'base' ? $event.target.value : null; 
                                syncCartItem(product);
                            ">
                                <option value="base" :data-price="product.selling_price"
                                    :data-stock="product.available_stock"
                                    x-text="(product.base_unit_name || 'Piece') + ' - $' + Number(product.selling_price).toFixed(2)">
                                </option>
                                <template x-for="uom in product.uom_list" :key="uom.id">
                                    <option :value="uom.id" :data-price="uom.price"
                                        :data-stock="Math.floor(product.available_stock / uom.conversion)"
                                        x-text="uom.name + ' - $' + Number(uom.price).toFixed(2)"></option>
                                </template>
                            </select>
                            <div class="flex justify-between items-center">
                                <span class="text-base font-bold text-green-600 dark:text-green-400"
                                    x-text="'$' + Number(product._uomPrice || product.selling_price).toFixed(2)"></span>
                                <span class="text-xs text-gray-500 dark:text-zinc-400">Qty: <label
                                        class="font-semibold text-gray-900 dark:text-zinc-100"
                                        x-text="product._uomStock || product.available_stock"></label></span>
                            </div>
                        </div>
                        {{-- Normal Price --}}
                        <div x-show="!product.uom_list || product.uom_list.length === 0" class="flex justify-between">
                            <span class="text-sm font-bold text-green-600"
                                x-text="'$' + Number(product.selling_price).toFixed(2)"></span>
                            <span class="text-xs text-gray-500">Qty: <label class="font-semibold"
                                    x-text="product.available_stock"></label></span>
                        </div>
                    </div>

                    {{-- Add to Cart --}}
                    <div class="mt-2 pt-1.5 border-t border-gray-50 dark:border-zinc-800">
                        <button
                            @click="addToCart({ 
                            id: product.id, 
                            name: product.name, 
                            price: product._uomPrice || product.selling_price, 
                            image: product.image, 
                            stock: product._uomStock || product.available_stock,
                            uom_id: product._uomId || null,
                            base_unit: product._uomName || product.base_unit_name || 'piece'
                        })"
                            x-show="!cartItems.find(i => i.id === product.id)"
                            class="w-full py-1.5 text-xs font-medium text-white bg-[#1063a2] rounded hover:bg-[#0c4f82] transition">
                            <i class="bi bi-plus-lg"></i> Add to Order
                        </button>

                        {{-- Qty Controls --}}
                        <div x-show="cartItems.find(i => i.id === product.id)"
                            class="flex items-center justify-between">
                            <button @click="decreaseQty(cartItems.findIndex(i => i.id === product.id))"
                                class="px-2.5 flex-1 py-1.5 bg-red-500 text-white rounded text-xs font-bold">-</button>
                            <span x-text="cartItems.find(i => i.id === product.id)?.qty || 0"
                                class="text-sm px-4 text-black dark:text-zinc-100 font-bold"></span>
                            <button
                                @click="addToCart({ 
                                id: product.id, 
                                name: product.name, 
                                price: product._uomPrice || product.selling_price, 
                                image: product.image, 
                                stock: product._uomStock || product.available_stock,
                                uom_id: product._uomId || null,    
                                base_unit: product._uomName || product.base_unit_name || 'piece'
                            })"
                                class="px-2.5 flex-1 py-1.5 bg-green-600 text-white rounded text-xs font-bold">+</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty state: Not Found via search --}}
        <div x-show="filteredProducts.length === 0 && products.length > 0 "
            class="bg-gray-200/50 dark:bg-zinc-900 col-span-full flex flex-col items-center justify-center py-12">
            <div class="rounded-full bg-gray-100 dark:bg-zinc-800 p-4 mb-4">
                <x-heroicon-o-magnifying-glass class="w-10 h-10 text-gray-400 dark:text-zinc-600" />
            </div>
            <h4 class="text-base font-semibold text-gray-800 dark:text-zinc-200">No Results Found</h4>
            <p class="text-xs text-center text-gray-500 dark:text-zinc-400 max-w-[240px] mt-1">
                Sorry, we couldn't find any products matching your search.
            </p>
        </div>
        {{-- Empty state: Category truly empty (no search) --}}
        <div x-show="products.length === 0"
            class="bg-gray-200/50 dark:bg-zinc-900 col-span-full flex flex-col items-center justify-center py-12">
            <div class="rounded-full bg-gray-100 dark:bg-zinc-800 p-4 mb-4">
                <x-heroicon-o-shopping-bag class="w-10 h-10 text-gray-400 dark:text-zinc-600" />
            </div>
            <h4 class="text-base font-semibold text-gray-800 dark:text-zinc-200">No Products Added Yet</h4>
            <p class="text-xs text-center text-gray-500 dark:text-zinc-400 max-w-[240px] mt-1">
                There are currently no products registered in this category.
            </p>
        </div>

    </div>
</div>
