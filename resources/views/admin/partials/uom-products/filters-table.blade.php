{{-- Header --}}
<div class="mb-4 flex flex-row items-center justify-between">
    <div>
        <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-zinc-100">UOM Product List</h2>
        <p class="text-xs text-gray-500 dark:text-zinc-400">Manage Unit of Measurement assignments for your products.</p>
    </div>
    <button @click="openUomForm(null)"
        class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition leading-tight">
        <i class="fa-solid fa-plus"></i>
        <span>Add Product UOM</span>
    </button>
</div>

{{-- UOM Table --}}
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="pb-2 pr-4 font-medium">Product</th>
                    <th class="pb-2 px-4 font-medium">Category</th>
                    <th class="pb-2 px-4 font-medium text-right">Price</th>
                    <th class="pb-2 px-4 font-medium text-center">Base Unit</th>
                    <th class="pb-2 px-4 font-medium text-center">UOMs</th>
                    <th class="pb-2 px-4 font-medium text-center">Status</th>
                    <th class="pb-2 px-4 font-medium">Date</th>
                    <th class="pb-2 pl-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                <template x-for="product in uomProducts" :key="product.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">

                        {{-- Product Image & Name --}}
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3">
                                <img :src="product.image ||
                                    'https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png'"
                                    class="w-12 h-12 bg-[#0F6E8C]/10 dark:bg-[#0F6E8C]/20 rounded-xs shrink-0 object-cover" />
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-zinc-200 line-clamp-3"
                                        x-text="product.name"></p>
                                    <p class="text-xs text-gray-400 dark:text-zinc-500" x-text="product.code"></p>
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td class="py-3 px-4 text-gray-600 dark:text-zinc-400 text-xs"
                            x-text="product.category?.name || 'Unassigned'"></td>

                        {{-- Price --}}
                        <td class="py-3 px-4 text-right font-medium text-gray-800 dark:text-zinc-200 text-xs"
                            x-text="'$' + Number(product.selling_price || 0).toFixed(2)"></td>

                        {{-- Base Unit --}}
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span
                                class="px-2 py-0.5 text-[12px] font-semibold rounded-full bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400"
                                x-text="product.uoms?.find(u => u.pivot?.is_default)?.name || product.uoms?.[0]?.name || 'PCS'">
                            </span>
                        </td>

                        {{-- UOM unit --}}
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <template x-for="uom in product.uoms.filter(u => !u.is_default)" :key="uom.id">
                                    <span
                                        class="px-2 py-0.5 text-[12px] font-medium bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-300 rounded">

                                        <span x-text="uom.quantity_per_unit"></span> <span
                                            x-text="uom.name || 'Unit'"></span> -
                                        $<span x-text="parseFloat(uom.price).toFixed(2)"></span>
                                    </span>
                                </template>
                                <span x-show="!product.uoms || product.uoms.filter(u => !u.is_default).length === 0"
                                    class="text-xs text-gray-400">-</span>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="py-3 text-center">
                            <span class="px-2 py-0.5 text-[12px] font-semibold rounded-full capitalize"
                                :class="product.status === 'active' ?
                                    'bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400' :
                                    'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-500'"
                                x-text="product.status || 'inactive'">
                            </span>
                        </td>

                        {{-- Timestamps --}}
                        <td class="py-3 px-4 text-xs whitespace-nowrap">
                            <p class="text-gray-500 dark:text-zinc-500">Created
                                <label class="text-gray-600 dark:text-zinc-500 font-semibold"
                                    x-text="product.created_at ? new Date(product.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '-'"></label>
                            </p>
                            <p class="text-gray-500 dark:text-zinc-500">Updated
                                <label class="text-gray-600 dark:text-zinc-500 font-semibold"
                                    x-text="product.updated_at ? new Date(product.updated_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '-'"></label>
                            </p>
                        </td>

                        {{-- Actions --}}
                        <td class="py-3 pl-2">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openUomForm(product)" type="button"
                                    class="text-gray-400 dark:text-zinc-500 hover:text-[#0F6E8C] dark:hover:text-[#0F6E8C]"
                                    title="Edit">
                                    <x-heroicon-m-pencil-square class="w-5 h-5" />
                                </button>
                                <button @click="deleteUom(product.id)" type="button"
                                    class="trash-btn text-red-500 hover:text-red-600" title="Delete">
                                    <x-heroicon-m-trash class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Empty State --}}
    <template x-if="!uomProducts || uomProducts.length === 0">
        <div class="py-8 text-center">
            <div
                class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No products yet</h3>
            <p class="text-xs text-gray-400 dark:text-zinc-500">Start by adding your first product UOM.</p>
            <button @click="openUomForm(null)"
                class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition">
                <i class="fa-solid fa-plus text-[10px]"></i> Add Product UOM
            </button>
        </div>
    </template>
</div>
