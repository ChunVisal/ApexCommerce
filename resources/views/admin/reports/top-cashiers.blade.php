<div class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Top Cashiers</h3>
    </div>
    <x-table headers="headers.topCashiers" rows="paginatedTopCashiers">
        <td class="py-3 text-left">
            <span class="text-xs font-bold"
                :class="{
                    'text-yellow-500': index === 0,
                    'text-gray-400': index === 1,
                    'text-amber-600': index === 2,
                    'text-gray-500': index > 2
                }"
                x-text="'#' + (index + 1)"></span>
        </td>
        <td class="py-3 text-center">
            <div class="flex items-center gap-3">
                <div class="w-[48px] h-[48px] rounded-full text-sm text-white bg-[#0F6E8C]">
                    <img x-show="row.avatar" :src="row.avatar" :alt="row.name"
                        class="rounded-full w-full h-full object-cover">
                    <span x-show="!row.avatar" x-text="row.name.charAt(0).toUpperCase()"></span>
                </div>
                <div class="text-start">
                    <p class="font-medium text-gray-800 dark:text-zinc-200" x-text="row.name"></p>
                    <p class="text-[12px] text-gray-500 dark:text-zinc-300"
                        x-text="(row.employee_id ?? 'No ID') + ' · ' + (row.shift ?? '-')"></p>
                </div>
            </div>
        </td>
        <td class="py-3 text-center font-medium text-gray-800 dark:text-zinc-200" x-text="row.orders"></td>
        <td class="py-3 text-center text-gray-700 dark:text-zinc-300" x-text="row.items_sold ?? 0"></td>
        <td class="py-3 text-center font-semibold text-gray-800 dark:text-zinc-200"
            x-text="'$' + Number(row.revenue).toFixed(2)"></td>
        <td class="py-3 text-center font-medium text-red-500" x-text="'-$' + Number(row.discount ?? 0).toFixed(2)">
        </td>
        <td class="py-3 text-center text-gray-600 dark:text-zinc-400" x-text="'$' + Number(row.avg_order).toFixed(2)">
        </td>
        <td class="py-3 text-right font-bold text-[#0F6E8C]"
            x-text="'$' + Number(row.revenue - (row.discount ?? 0)).toFixed(2)"></td>
        <x-slot:empty>
            <x-empty-state icon="heroicon-o-user-group" x-show="topCashiers.length === 0" title="No top cashier found"
                description="No cashier has sales in the selected date range." />
        </x-slot:empty>
    </x-table>
</div>
