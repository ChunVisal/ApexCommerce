@extends('layouts.app')

@section('content')
    @include('admin.financial-movement.scripts')
    <div class="p-5" x-data="financialPage()">
        <x-breadcrumb :breadcrumbs="[
            '/' => 'Dashboard',
            '/admin/inventory' => 'Inventory',
            '/admin/inventory/financial-movements' => 'Financial Movements',
        ]" />
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">Financial Movements</h1>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                    {{ \Carbon\Carbon::parse($start)->format('M d, Y') }} —
                    {{ \Carbon\Carbon::parse($end)->format('M d, Y') }}
                </p>
            </div>
            <div class="flex gap-2 items-center">
                <x-date-range-picker route="admin.inventory.financial-movements" />
                <x-export-button :route="route('admin.inventory.financial-movements.export')" />
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <x-search-input placeholder="Search by name, categories, amount, user..." />
            <x-filter-select model="filterType">
                <option value="">All Types</option>
                <option value="in">Money In</option>
                <option value="out">Money Out</option>
            </x-filter-select>

            <x-filter-select model="filterCategory">
                <option value="">All Categories</option>
                <option value="sale">Sale</option>
                <option value="refund">Refund</option>
                <option value="purchase">Purchase</option>
                <option value="expense">Expense</option>
            </x-filter-select>

            <x-filter-select model="filterUser">
                <option value="">All Users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                @endforeach
            </x-filter-select>
        </div>

        {{-- Table --}}
        <div
            class="bg-white dark:bg-zinc-900 pb-4 px-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
            <div class="scroll-smooth table-scroll overflow-auto max-h-[600px]" x-ref="tableBody">
                <table class="w-full text-sm text-left">
                    <thead class="sticky top-0 z-10 bg-white dark:bg-zinc-900">
                        <tr
                            class="text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800/80">
                            <th class="py-3 px-4 font-medium whitespace-nowrap">Date</th>
                            <th class="py-3 px-4 font-medium">Type</th>
                            <th class="py-3 px-4 font-medium">Category</th>
                            <th class="py-3 px-4 font-medium">Net Amount</th>
                            <th class="py-3 px-4 font-medium">Amount</th>
                            <th class="py-3 px-4 font-medium">Reference</th>
                            <th class="py-3 px-4 font-medium">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                        <template x-for="m in paginatedMovements" :key="m.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="py-3 px-4 text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                                    x-text="new Date(m.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })">
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                                        :class="m.type === 'in' ?
                                            'bg-green-50 dark:bg-green-950/30 text-green-600 dark:text-green-400' :
                                            'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400'"
                                        x-text="m.type === 'in' ? 'IN' : 'OUT'"></span>
                                </td>
                                <td class="py-3 px-4 text-xs capitalize text-gray-700 dark:text-zinc-300"
                                    x-text="m.category"></td>
                                <td class="py-3 px-4 text-gray-700 dark:text-zinc-300 font-semibold whitespace-nowrap"
                                    x-text="m.net_amount != null ? '$' + m.net_amount : '-'"></td>
                                <td class="py-3 px-4  font-semibold whitespace-nowrap"
                                    :class="m.type === 'in' ? 'text-green-600 dark:text-green-400' :
                                        'text-red-600 dark:text-red-400'"
                                    x-text="(m.type === 'in' ? '+$' : '-$') + m.amount"></td>
                                <td class="py-3 px-4 text-xs text-gray-800 dark:text-zinc-300 whitespace-nowrap"
                                    x-text="m.reference || '-'"></td>
                                {{-- Authorized User Metadata Structure Layout --}}
                                <td class="py-3 px-4 text-xs text-left">
                                    <div class="min-w-[140px]">
                                        <p class="font-medium text-gray-800 dark:text-zinc-300"
                                            x-text="m.user?.name || '-'">
                                        </p>
                                        <p class="text-gray-400 dark:text-zinc-500 scale-95 origin-left mt-0.5"
                                            x-text="m.user?.email || '-'">
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- Empty State Row --}}
                        <tr x-show="filteredMovements.length === 0">
                            <td colspan="7" class="text-center py-16 bg-white dark:bg-zinc-900">
                                <div class="max-w-xs mx-auto flex flex-col items-center justify-center">
                                    {{-- Circular minimalist movement path icon container --}}
                                    <div
                                        class="w-11 h-11 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-zinc-500" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 2.75v18.5" />
                                            <path
                                                d="M15.75 6.5c0-1.65-1.73-2.75-3.75-2.75s-3.75 1.1-3.75 2.75c0 1.8 1.73 2.6 3.75 3.1s3.75 1.3 3.75 3.1c0 1.7-1.73 2.75-3.75 2.75s-3.75-1.1-3.75-2.75" />
                                        </svg>
                                    </div>

                                    {{-- Typography hierarchy --}}
                                    <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                                        No Financial Movements
                                    </p>
                                    <p
                                        class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[200px] leading-relaxed">
                                        There are no registered financial transactions for this filter.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <x-pagination />
        </div>
    </div>
@endsection
