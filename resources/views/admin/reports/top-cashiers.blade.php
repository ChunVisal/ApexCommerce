<div class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Top Cashiers</h3>
    </div>
    <div class="p-4 rounded-md shadow-xs border border-gray-200 dark:border-zinc-800/60">
        <x-table headers="headers.topCashiers" rows="topCashiers" as="cashier">
            <td class="text-left">
                <span class="text-xs font-bold"
                    :class="{
                        'text-yellow-500': index === 0,
                        'text-gray-400': index === 1,
                        'text-amber-600': index === 2,
                        'text-gray-500': index > 2
                    }"
                    x-text="'#' + (index + 1)"></span>
            </td>
            <td class=" text-center py-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-[48px] h-[48px] rounded-full text-sm text-white bg-[#0F6E8C]">
                        <img x-show="cashier.avatar" :src="cashier.avatar" :alt="cashier.name"
                            class="rounded-full w-full h-full object-cover">
                        <span x-show="!cashier.avatar" x-text="cashier.name.charAt(0).toUpperCase()"></span>
                    </div>
                    <div class="text-start">
                        <p class="font-medium text-gray-800 dark:text-zinc-200" x-text="cashier.name"></p>
                        <p class="text-[12px] text-gray-500 dark:text-zinc-300"
                            x-text="(cashier.employee_id ?? 'No ID') + ' · ' + (cashier.shift ?? '-')"></p>
                    </div>
                </div>
            </td>
            <td class="text-center font-medium text-gray-800 dark:text-zinc-200" x-text="cashier.orders"></td>
            <td class="text-center text-gray-700 dark:text-zinc-300" x-text="cashier.items_sold ?? 0"></td>
            <td class="text-center font-semibold text-gray-800 dark:text-zinc-200"
                x-text="'$' + Number(cashier.revenue).toFixed(2)"></td>
            <td class="text-center font-medium text-red-500" x-text="'-$' + Number(cashier.discount ?? 0).toFixed(2)">
            </td>
            <td class="text-center text-gray-600 dark:text-zinc-400"
                x-text="'$' + Number(cashier.avg_order).toFixed(2)">
            </td>
            <td class="text-right font-bold text-[#0F6E8C]"
                x-text="'$' + Number(cashier.revenue - (cashier.discount ?? 0)).toFixed(2)"></td>
        </x-table>
    </div>
    <x-empty-state icon="heroicon-o-user-group" x-show="topCashiers.length === 0" title="No top cashier found"
        description="No cashier has sales in the selected date range." />
</div>
