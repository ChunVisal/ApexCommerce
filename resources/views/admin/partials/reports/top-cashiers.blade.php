<div class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">Top Cashiers</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="py-3 pl-4 font-medium w-10">#</th>
                    <th class="py-3 px-4 font-medium">Cashier</th>
                    <th class="py-3 px-4 font-medium text-center">Orders</th>
                    <th class="py-3 px-4 font-medium text-center">Items Sold</th>
                    <th class="py-3 px-4 font-medium text-right">Revenue</th>
                    <th class="py-3 px-4 font-medium text-right">Discounts</th>
                    <th class="py-3 px-4 font-medium text-right">Avg Order</th>
                    <th class="py-3 pr-4 font-medium text-right">Net Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                @php $maxRevenue = $topCashiers->max('revenue') ?: 1; @endphp
                @foreach ($topCashiers as $index => $cashier)
                    @php
                        $rank = $index + 1;
                        $percent = round(($cashier->revenue / $maxRevenue) * 100);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pl-4">
                            <span
                                class="text-xs font-bold 
                            {{ $rank == 1 ? 'text-yellow-500' : ($rank == 2 ? 'text-p' : ($rank == 3 ? 'text-amber-600' : 'text-gray-500')) }}">
                                #{{ $rank }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-[48px] h-[48px] rounded-full flex items-center justify-center font-bold text-sm text-white shrink-0 overflow-hidden bg-[#0F6E8C]">
                                    @if ($cashier->avatar)
                                        <img src="{{ $cashier->avatar }}" class="w-full h-full object-cover"
                                            alt="{{ $cashier->name }}">
                                    @else
                                        {{ strtoupper(substr($cashier->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-zinc-200">{{ $cashier->name }}</p>
                                    <p class="text-[12px] text-gray-500 dark:text-zinc-300">
                                        {{ $cashier->employee_id ?? 'No ID' }} ·
                                        {{ $cashier->shift ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center font-medium text-gray-800 dark:text-zinc-200">
                            {{ $cashier->orders }}</td>
                        <td class="py-3 px-4 text-center text-gray-700 dark:text-zinc-300">
                            {{ $cashier->items_sold ?? 0 }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-800 dark:text-zinc-200">
                            ${{ number_format($cashier->revenue, 2) }}</td>
                        <td class="py-3 px-4 font-medium text-right text-red-500">
                            -${{ number_format($cashier->discount ?? 0, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-gray-600 dark:text-zinc-400">
                            ${{ number_format($cashier->avg_order, 2) }}</td>
                        <td class="py-3 pr-4 text-right font-bold text-[#0F6E8C]">
                            ${{ number_format($cashier->revenue - ($cashier->discount ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
