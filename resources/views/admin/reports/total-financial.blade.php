<div class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">All Financial Movements</h3>
        <div class="flex items-center gap-3 text-xs">
            <span class="text-gray-600 dark:text-zinc-400"> {{ $financials->count() }} total</span>
            <span class="text-green-600">
                {{ $financials->where('type', 'in')->count() }} money in</span>
            <span class="text-red-500"> {{ $financials->where('type', 'out')->count() }} money
                out</span>
        </div>
    </div>

    <div class="overflow-x-auto max-h-[600px] overflow-y-auto tab-container">
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white dark:bg-zinc-900">
                <tr
                    class="text-left text-xs text-gray-600 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="py-3 pl-4 font-medium">Reference</th>
                    <th class="py-3 px-3 font-medium">User</th>
                    <th class="py-3 font-medium">Category</th>
                    <th class="py-3 px-3 font-medium">Source</th>
                    <th class="py-3 px-3 font-medium text-center">Net Amount</th>
                    <th class="py-3 px-3 font-medium text-center">Amount</th>
                    <th class="py-3 pr-4 font-medium text-center">Type</th>
                    <th class="py-3 px-3 font-medium pl-14 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                @forelse($financials as $m)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                        <td class="py-3 pl-4 font-medium text-gray-800 dark:text-zinc-200 text-xs">
                            {{ $m->reference ?? '-' }}
                        </td>
                        <td class="py-3 px-3 text-xs font-medium text-gray-600 dark:text-zinc-400">
                            {{ $m->user->name ?? '-' }}
                        </td>
                        <td class="py-3">
                            <span
                                class="px-1.5 py-0.5 text-[11px] rounded-full font-medium capitalize
                            {{ $m->category === 'sale'
                                ? 'bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-400 border dark:border-green-800/50'
                                : ($m->category === 'purchase'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border dark:border-blue-800/50'
                                    : ($m->category === 'refund'
                                        ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 border dark:border-yellow-800/50'
                                        : 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border dark:border-purple-800/50')) }}">
                                {{ $m->category }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-xs text-gray-600 dark:text-zinc-400">
                            {{ $m->source_type ? class_basename($m->source_type) . ' #' . $m->source_id : '-' }}
                        </td>
                        <td class="py-3 px-3 text-center text-xs text-gray-600 dark:text-zinc-400">
                            {{ $m->net_amount !== null ? '$' . number_format($m->net_amount, 2) : '-' }}
                        </td>
                        <td
                            class="py-3 px-3 text-center text-xs font-semibold
                        {{ $m->type === 'in' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $m->type === 'in' ? '+' : '-' }}${{ number_format($m->amount, 2) }}
                        </td>
                        <td class="py-3 pr-4 text-center">
                            <span
                                class="px-1.5 py-0.5 text-[11px] rounded-full font-medium
                            {{ $m->type === 'in'
                                ? 'bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-400 border dark:border-green-800/50'
                                : 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400 border dark:border-red-800/50' }}">
                                {{ $m->type === 'in' ? 'Money In' : 'Money Out' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-xs text-gray-500 dark:text-zinc-500">
                            {{ $m->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-400 dark:text-zinc-500 text-sm">
                            No financial movements found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
