{{-- resources/views/components/pagination.blade.php --}}
<div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-200 dark:border-zinc-800">
    <p class="text-xs text-gray-500 dark:text-zinc-400" x-text="showingText"></p>
    <div class="flex items-center gap-1">
        <button @click="prevPage()" :disabled="currentPage === 1" type="button"
            class="px-3 py-1 text-xs border border-gray-300 dark:border-zinc-700 rounded-md text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition disabled:opacity-50">Previous</button>
        <template x-for="page in pageNumbers" :key="page">
            <div>
                <button x-show="page !== '...'" @click="goToPage(page)" type="button"
                    :class="currentPage === page ? 'bg-[#0F6E8C] text-white' :
                        'border border-gray-300 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700'"
                    class="px-3 py-1 text-xs rounded-md transition">
                    <span x-text="page"></span>
                </button>
                <span x-show="page === '...'" class="px-2 text-gray-400">...</span>
            </div>
        </template>
        <button @click="nextPage()" :disabled="currentPage === totalPages" type="button"
            class="px-3 py-1 text-xs border border-gray-300 dark:border-zinc-700 rounded-md text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition disabled:opacity-50">Next</button>
    </div>
</div>
