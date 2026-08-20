{{-- resources/views/admin/partials/uom-products/slide-over-uom.blade.php --}}
<div x-show="uomFormOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

    <div x-show="uomFormOpen" x-transition.opacity @click="closeUomPanel()"
        class="absolute inset-0 bg-gray-900/40 dark:bg-black/60"></div>

    <div x-show="uomFormOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col border-l border-gray-300 dark:border-zinc-800">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300 dark:border-zinc-800">
            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-100"
                    x-text="editMode ? 'Edit UOM Product' : 'Add UOM Product'"></h2>
            </div>
            <button @click="closeUomPanel()" type="button"
                class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300">
            <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form @submit.prevent="submitUomForm()" x-ref="uomProductForm" class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto tab-container px-5 py-4 space-y-4">

                {{-- Category --}}
                <div>
                    <label
                        class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">Category
                        *</label>
                    <select x-model="form.category_code" @change="loadProducts()" required
                        class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->code }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Product Name --}}
                <div>
                    <label
                        class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">
                        Product Name *
                    </label>

                    {{-- Select from existing --}}
                    <select x-model="selectedProductName" @change="form.name = $event.target.value; autoFillDetails()"
                        :disabled="!form.category_code"
                        class=" disabled:cursor-not-allowed w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] disabled:opacity-50">
                        <option value="">Select product from catalog</option>
                        <template x-for="product in categoryProducts" :key="product.name">
                            <option :value="product.name" x-text="product.name"></option>
                        </template>
                    </select>

                    <div class="relative text-center">
                        <span class="bg-white dark:bg-zinc-900 px-2 text-[10px] text-gray-400 uppercase">OR</span>
                        <div class="absolute inset-0 flex items-center -z-10">
                            <div class="w-full border-t border-gray-300 dark:border-zinc-700"></div>
                        </div>
                    </div>

                    {{-- Manual input --}}
                    <input type="text" :disabled="!form.category_code" x-model="form.name"
                        placeholder="Type new product name..." @input="selectedProductName = ''"
                        class=" disabled:opacity-50 disabled:cursor-not-allowed placeholder:text-gray-100  w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
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
                    <input type="url" x-model="form.image_url"
                        @input="form.image_preview = ''; form.image_file = null;" placeholder="Paste image URL..."
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

                {{-- Units Configuration (always visible — this form is UOM-only) --}}
                <div class="pt-2 space-y-4">

                    {{-- Section Header --}}
                    <div class="flex items-center justify-between pb-2 border-b border-gray-300 dark:border-zinc-800">
                        <label class=" text-[12px] font-bold tracking-wider uppercase text-gray-700 dark:text-zinc-200">
                            Units Configuration
                        </label>
                        <button type="button"
                            @click="uomFormList.push({ uom_id: '', name: '', code: '', quantity_per_unit: 1, price: 0, is_default: false })"
                            class="text-sm font-semibold text-p hover:underline flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Add Unit</span>
                        </button>
                    </div>

                    {{-- Single Main Card Block --}}
                    <div
                        class="p-4 border border-gray-300 dark:border-zinc-800 rounded-lg bg-gray-50 dark:bg-zinc-900 space-y-5">

                        {{-- BASE UNIT ITEM --}}
                        <div class="space-y-4 pb-4 border-b-2 border-gray-300 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-p uppercase tracking-wide">Base Unit</span>
                                <span
                                    class="text-xs font-semibold text-gray-800 dark:text-zinc-300 bg-blue-100/30 border border-gray-300 dark:bg-zinc-800/20 dark:border-zinc-700 px-2.5 py-0.5 rounded">
                                    Default Base
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                {{-- Unit Type --}}
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1.5 block">Unit
                                        Type</label>
                                    <select x-model="form.base_unit_name"
                                        class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-gray-500 dark:focus:border-zinc-500">
                                        <option value="">Select unit</option>
                                        <option value="Piece">Piece</option>
                                        <option value="Gram">Gram</option>
                                        <option value="Meter">Meter</option>
                                        <option value="Kilogram">Kilogram</option>
                                    </select>
                                </div>
                                {{-- Stock Qty --}}
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1.5 block">Stock
                                        Qty</label>
                                    <input type="number" min="0" x-model="form.stock" min="1"
                                        :max="form.stock || 1" :disabled="editMode" :readonly="editMode"
                                        :class="editMode ? 'bg-gray-100 dark:bg-zinc-800 cursor-not-allowed' :
                                            'bg-white dark:bg-zinc-800'"
                                        class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:border-gray-500 dark:focus:border-zinc-500"
                                        placeholder="0">
                                    <p x-show="editMode"
                                        class="text-[10px] text-yellow-600 dark:text-yellow-500 mt-1">Stock can
                                        only be adjusted in
                                        Inventory</p>
                                </div>
                            </div>


                            {{-- Row 2: 2 Inputs (Code & Price) --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1.5">Code</label>
                                    <input type="text" x-model="form.base_unit_code"
                                        placeholder="m = meter, g = gram ,p..."
                                        class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:border-gray-500 dark:focus:border-zinc-500">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1.5">Price
                                        ($) *</label>
                                    <input type="number" x-model="form.price" step="0.01" placeholder="0.00"
                                        required @input="updateUomPrices()"
                                        class="w-full text-sm text-right border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:border-gray-500 dark:focus:border-zinc-500">
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 dark:text-zinc-400 italic">
                                1 <span class="font-semibold text-p" x-text="form.base_unit_name || 'unit'"></span> =
                                1 stock. This cannot be changed.
                            </p>
                        </div>

                        <template x-if="uomFormList.length === 0">
                            <p class="text-xs text-gray-400 dark:text-zinc-500 italic py-2">
                                No additional units yet. Click "<button type="button"
                                    @click="uomFormList.push({ uom_id: '', name: '', code: '', quantity_per_unit: 1, price: 0, is_default: false })"
                                    class="text-p italic underline">
                                    <span>Add Unit</span>
                                </button>" to add one.
                            </p>
                        </template>


                        {{-- ADDITIONAL UNITS LIST --}}
                        <template x-for="(uom, index) in uomFormList" :key="index">
                            <div
                                class="space-y-4 pb-4 border-b border-gray-200 dark:border-zinc-800 last:border-0 last:pb-0">

                                {{-- Header Actions --}}
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold text-p dark:text-zinc-300 uppercase tracking-wide">Additional
                                        UOM</span>
                                    <button type="button" @click="uomFormList.splice(index, 1)"
                                        class="text-red-500 hover:text-red-400 transition" title="Delete Unit">
                                        <x-heroicon-m-trash class="w-5 h-5" />
                                    </button>
                                </div>

                                {{-- Row 1: 1 Input (Selling Unit Select) --}}
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1.5">Unit
                                        Name</label>
                                    <select x-model="uom.name"
                                        class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-gray-500 dark:focus:border-zinc-500">
                                        <option value="">Select selling unit</option>
                                        <option value="Tube">Tube</option>
                                        <option value="Box">Box</option>
                                        <option value="Pack">Pack</option>
                                        <option value="Carton">Carton</option>
                                        <option value="Roll">Roll</option>
                                        <option value="Bundle">Bundle</option>
                                    </select>
                                </div>

                                {{-- Row 2: 2 Inputs (Contains Qty & Calculated Price Input) --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1.5 block">
                                            Contains (in <span class="text-p"
                                                x-text="form.base_unit_name || 'units'"></span>)
                                        </label>
                                        <input type="number" x-model="uom.quantity_per_unit" min="1"
                                            placeholder="Qty base"
                                            @input="uom.price = (uom.quantity_per_unit * form.price).toFixed(2)"
                                            class="w-full text-sm border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:border-gray-500 dark:focus:border-zinc-500">

                                        <p x-show="Number(uom.quantity_per_unit) > Number(form.stock || 0)"
                                            class="text-[11px] text-red-500 mt-1 whitespace-nowrap">
                                            <x-heroicon-m-exclamation-triangle
                                                class="inline w-4 h-4 text-yellow-500" />
                                            cannot greater than current stock (max:
                                            <span x-text="form.stock"></span>)
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1.5">Auto
                                            Price
                                            ($)</label>
                                        <input type="number" x-model="uom.price" step="0.01" placeholder="0.00"
                                            class="w-full text-sm text-right border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 font-bold placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:border-gray-500 dark:focus:border-zinc-500">
                                    </div>

                                    <p class="text-xs text-gray-500 dark:text-zinc-400 italic">1 <span
                                            class="text-gray-800 dark:text-zinc-200"
                                            x-text="uom.name || 'unit'"></span> =
                                        <strong class="text-p" x-text="uom.quantity_per_unit || 1">.</strong>
                                        <span class="text-gray-800 dark:text-zinc-200"
                                            x-text="form.base_unit_name"></span>
                                    </p>

                                </div>

                                {{-- Description --}}
                                <div>
                                    <label
                                        class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">
                                        Description
                                    </label>
                                    <textarea x-model="uom.description" placeholder="Additional details or notes" rows="2"
                                        class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] resize-none"></textarea>
                                </div>
                            </div>
                        </template>

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

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-300 dark:border-zinc-800">
                <button @click="closeUomPanel()" type="button"
                    class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800">
                    Cancel
                </button>
                <button type="submit" :disabled="submitting"
                    class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md
                     hover:bg-[#0c5972] disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-1">
                    <i x-show="submitting" class="fa-solid fa-spinner fa-spin"></i>
                    <span
                        x-text="submitting ? (editMode ? 'Saving...' : 'Adding...') : (editMode ? 'Save Changes' : 'Add UOM Product')"></span>
                </button>
            </div>
        </form>
    </div>
</div>
