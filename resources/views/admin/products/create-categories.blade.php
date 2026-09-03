    <div x-show="openCategory" x-cloak class="fixed inset-0 z-[51] overflow-y-auto" style="display: none;">
        <div x-show="openCategory" x-transition.opacity @click="openCategory = false"
            class="absolute inset-0 bg-gray-900/40 dark:bg-black/60"></div>

        <div x-show="openCategory" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col border-l border-gray-300 dark:border-zinc-800">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300 dark:border-zinc-800">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-100"
                        x-text="editMode ? 'Edit Category' : 'Add Category'"></h2>
                    <p x-show="!editMode && draftList.length > 0" class="text-xs text-[#0F6E8C] mt-0.5"
                        x-text="draftList.length + ' product(s) in draft'"></p>
                </div>
                <button @click="openCategory = false" type="button"
                    class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form @submit.prevent="saveCategory" class="flex-1 flex flex-col">
                <div class="flex-1 px-5 py-4 space-y-4">
                    <div>
                        <label
                            class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">
                            Category Name *
                        </label>
                        <input type="text" x-model="categoryForm.name" placeholder="e.g. Graphics Card"
                            class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                    </div>

                    {{-- display svg up here --}}
                    <div x-show="categoryForm.svg" class="flex items-center gap-2"></div>
                    <div class="w-6 h-6 flex items-center justify-center">
                        <span x-html="categoryForm.svg"></span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-zinc-400">Preview</span>
                    <div>
                        <label
                            class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">
                            Icon (SVG) *
                        </label>
                        <input type="text" x-model="categoryForm.svg" placeholder="Paste SVG markup or class name"
                            class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                    </div>
                </div>
                <div
                    class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-300 dark:border-zinc-800">
                    <button @click="openCategory = false" type="button"
                        class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800">
                        Cancel
                    </button>
                    <button type="submit" :disabled="submittingCategory"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] disabled:opacity-60">
                        <span x-text="submittingCategory ? 'Saving...' : 'Add Category'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
