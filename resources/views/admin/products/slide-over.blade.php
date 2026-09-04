<x-slide-over>
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300 dark:border-zinc-800">
        <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-100"
                x-text="editMode ? 'Edit Product' : 'Add Product'"></h2>
            <p x-show="!editMode && draftList.length > 0" class="text-xs text-[#0F6E8C] mt-0.5"
                x-text="draftList.length + ' product(s) in draft'"></p>
        </div>
        <button @click="open = false" type="button"
            class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <form @submit.prevent="submitForm()" x-ref="productForm" class="flex-1 flex flex-col overflow-hidden">
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

            {{-- Category --}}
            <div class="mb-1">
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400">Category
                    *</label>
                <div class="flex items-center gap-2">
                    <select x-model="form.category_code" @change.one="loadProducts()"
                        class="flex-1 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                        <option value="">Select category</option>
                        <template x-for="cat in categories" :key="cat.code">
                            <option :value="cat.code" x-text="cat.name"></option>
                        </template>
                    </select>
                    <button type="button" @click="openAddCategory()"
                        class=" flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-p rounded-md hover:bg-[#1a627a] transition whitespace-nowrap">
                        <i class="fa-solid" :class="!editMode ? 'fa-plus' : 'fa-edit'"></i>
                        <span x-text="!editMode ? 'Add Category' : 'Edit Category'"></span>
                    </button>
                </div>
            </div>

            {{-- Product Name --}}
            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">
                    Product Name *
                </label>

                {{-- Searchable select from existing --}}
                <div x-data="{ search: '', open: false }" class="relative">
                    <div @click="form.category_code && (open = !open)"
                        class="w-full text-sm px-3 py-2 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-800 flex items-center justify-between transition-colors"
                        :class="!form.category_code ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                        <span x-text="selectedProductName || 'Select product from catalog'"
                            :class="!selectedProductName && 'text-gray-500 dark:text-zinc-400'"></span>
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 dark:text-zinc-400" />
                    </div>

                    {{-- Dropdown Menu --}}
                    <div x-show="open" @click.outside="open = false" x-cloak
                        class="absolute z-20 w-full tab-container overflow-x-hidden mt-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-md shadow-lg max-h-[300px] overflow-y-auto">

                        <input type="text" x-model="search" placeholder="Search product..."
                            class="sticky top-0 w-full text-sm border-b border-gray-200 dark:border-zinc-700 px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-500 dark:placeholder-zinc-400 focus:outline-none">

                        <template
                            x-for="product in categoryProducts.filter(p => !search || p.name.toLowerCase().includes(search.toLowerCase()))"
                            :key="product.name">
                            <div @click="selectedProductName = product.name; form.name = product.name; open = false; search = ''; autoFillDetails()"
                                class="px-3 py-2 text-sm text-gray-850 dark:text-zinc-200 hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer"
                                x-text="product.name">
                            </div>
                        </template>

                        {{-- Not found message --}}
                        <div x-show="search && categoryProducts.filter(p => p.name.toLowerCase().includes(search.toLowerCase())).length === 0"
                            class="px-3 py-4 text-center text-gray-400 dark:text-zinc-500 text-sm select-none">
                            No product found.
                        </div>
                    </div>
                </div>

                <div class="relative text-center my-2">
                    <span class="bg-white dark:bg-zinc-900 px-2 text-[10px] text-gray-400 uppercase">OR</span>
                    <div class="absolute inset-0 flex items-center -z-10">
                        <div class="w-full border-t border-gray-300 dark:border-zinc-700"></div>
                    </div>
                </div>

                {{-- Manual input --}}
                <input type="text" :disabled="!form.category_code" x-model="form.name"
                    placeholder="Type new product name..." @input="selectedProductName = ''"
                    class="disabled:opacity-50 disabled:cursor-not-allowed w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
            </div>

            {{-- Image --}}
            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">Product
                    Image</label>
                <div x-show="form.image_preview || form.image_url" class="mb-2 relative inline-block">
                    <img :src="form.image_preview || form.image_url"
                        class="h-24 w-24 object-cover rounded-md border border-gray-300 dark:border-zinc-700">
                    <button type="button"
                        @click="form.image_preview = ''; form.image_url = ''; form.image_file = null;"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <input type="url" x-model="form.image_url" @input="form.image_preview = ''; form.image_file = null;"
                    placeholder="Paste image URL..."
                    class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] mb-2">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex-1 h-px bg-gray-300 dark:bg-zinc-700"></div>
                    <span class="text-xs text-gray-400 dark:text-zinc-500">or upload file</span>
                    <div class="flex-1 h-px bg-gray-300 dark:bg-zinc-700"></div>
                </div>
                <label
                    class="flex items-center justify-center gap-2 w-full px-3 py-2 border border-dashed border-gray-300 dark:border-zinc-600 rounded-md cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                    <i class="fa-solid fa-arrow-up-from-bracket text-gray-400 text-sm"></i>
                    <span class="text-xs text-gray-500 dark:text-zinc-400"
                        x-text="form.image_file ? form.image_file.name : 'Click to upload image'"></span>
                    <input type="file" accept="image/*" class="hidden" @change.one="handleImageFile($event)">
                </label>
            </div>
            {{-- Price + Stock --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label
                        class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">Price
                        ($)
                        *</label>
                    <input type="number" step="0.01" x-model.number="form.price" placeholder="0.00"
                        class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                </div>
                <div>
                    <label
                        class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">Stock
                        *</label>
                    <input type="number" x-model.number="form.stock" placeholder="0" :disabled="editMode"
                        :readonly="editMode"
                        :class="editMode ? 'bg-gray-100 dark:bg-zinc-800 cursor-not-allowed' : 'bg-white dark:bg-zinc-800'"
                        class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                    <p x-show="editMode" class="text-[10px] text-yellow-600 dark:text-yellow-500 mt-1">Stock can
                        only be adjusted in
                        Inventory</p>
                </div>
            </div>

            {{-- Status --}}
            <div class="flex items-center justify-between">
                <label
                    class="text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400">Status</label>
                <div class="flex gap-1">
                    <button type="button" @click="form.status = 'active'"
                        class="px-3 py-1 text-[11px] font-medium rounded-l-md transition"
                        :class="form.status === 'active' ? 'bg-green-500 text-white' :
                            'bg-gray-300 dark:bg-zinc-700 text-gray-500 dark:text-zinc-400'">
                        Active
                    </button>
                    <button type="button" @click="form.status = 'inactive'"
                        class="px-3 py-1 text-[11px] font-medium rounded-r-md transition"
                        :class="form.status === 'inactive' ? 'bg-red-500 text-white' :
                            'bg-gray-300 dark:bg-zinc-700 text-gray-500 dark:text-zinc-400'">
                        Inactive
                    </button>
                </div>
            </div>

            <div x-show="!editMode">
                <button type="button" id="addToDraftBtn" @click.prevent="addToDraft()"
                    :class="draftEditIndex !== null ?
                        'text-green-600 border-[#0F6E8C] hover:bg-green-50 dark:hover:bg-[#0F6E8C]/20' :
                        'text-[#0F6E8C] border-[#0F6E8C] hover:bg-[#0F6E8C]/10'"
                    class="w-full px-4 py-2 text-xs font-semibold border rounded-md transition flex items-center justify-center gap-2">
                    <i x-show="draftEditIndex === null" class="fa-solid fa-plus"></i>
                    <i x-show="draftEditIndex !== null" class="fa-solid fa-check"></i>
                    <span x-text="draftEditIndex !== null ? 'Save to Draft' : 'Add to Draft'"></span>
                </button>
            </div>

            {{-- Draft List --}}
            <div x-show="!editMode && draftList.length > 0" class="space-y-2">
                <p
                    class="text-xs font-semibold text-gray-600 dark:text-zinc-400 border-b border-gray-300 dark:border-zinc-700 pb-1">
                    Draft List (<span x-text="draftList.length"></span>)
                </p>
                <template x-for="(item, index) in draftList" :key="item._id">
                    <div
                        class="flex items-center justify-between bg-gray-50 dark:bg-zinc-800 rounded-md px-3 py-2 gap-2">

                        {{-- Image --}}
                        <div class="w-10 h-10 shrink-0 rounded overflow-hidden bg-gray-300 dark:bg-zinc-700">
                            <img :src="item.image_url || item.image_preview ||
                                'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                                style="width:100%;height:100%;object-fit:cover;">
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-bold tracking-wider uppercase text-gray-800 dark:text-zinc-100 truncate"
                                x-text="item.name"></p>
                            <p class="text-[10px] text-gray-400 dark:text-zinc-500">
                                $<span x-text="item.price"></span> · Stock: <span x-text="item.stock"></span>
                            </p>
                            {{-- Status badge --}}
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                                :class="item.status === 'active' ?
                                    'bg-green-50 text-green-600 dark:bg-green-950/40 dark:text-green-400' :
                                    'bg-gray-100 text-gray-500 dark:bg-zinc-700 dark:text-zinc-400'"
                                x-text="item.status === 'active' ? 'Active' : 'Inactive'">
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="editDraft(index)"
                                class="text-gray-400 hover:text-[#0F6E8C] transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <button type="button" @click="removeDraft(index)"
                                class="text-red-400 hover:text-red-600 transition">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-300 dark:border-zinc-800">
            <button @click="open = false" type="button"
                class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800">
                Cancel
            </button>
            <button type="submit" :disabled="submitting"
                class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md
                     hover:bg-[#0c5972] disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-1">
                <i x-show="submitting" class="fa-solid fa-spinner fa-spin"></i>
                <span
                    x-text="submitting ? (editMode ? 'Saving...' : 'Adding...') : (editMode ? 'Save Changes' : draftList.length > 0 ? 'Submit All (' + draftList.length + ')' : 'Add Product')"></span>
            </button>
        </div>
    </form>
</x-slide-over>
