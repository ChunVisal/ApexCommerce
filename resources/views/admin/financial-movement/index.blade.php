@extends('layouts.app')
@section('content')
    @include('admin.financial-movement.scripts')
    <div class="" x-data="financialPage()">

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
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
            <div class="scroll-smooth table-scroll overflow-auto max-h-[600px]" x-ref="tableBody">
                <x-table rows="paginatedMovements" as="m">
                    <td class="text-left text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                        x-text="new Date(m.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })">
                    </td>
                    <td class="text-center ">
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                            :class="m.type === 'in' ?
                                'bg-green-50 dark:bg-green-950/30 text-green-600 dark:text-green-400' :
                                'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400'"
                            x-text="m.type === 'in' ? 'IN' : 'OUT'"></span>
                    </td>
                    <td class="text-center text-xs capitalize text-gray-700 dark:text-zinc-300" x-text="m.category"></td>
                    <td class="text-center text-gray-700 dark:text-zinc-300 font-semibold whitespace-nowrap"
                        x-text="m.net_amount != null ? '$' + m.net_amount : '-'"></td>
                    <td class="text-center  font-semibold whitespace-nowrap"
                        :class="m.type === 'in' ? 'text-green-600 dark:text-green-400' :
                            'text-red-600 dark:text-red-400'"
                        x-text="(m.type === 'in' ? '+$' : '-$') + m.amount"></td>
                    <td class="text-center text-xs text-gray-800 dark:text-zinc-300 whitespace-nowrap"
                        x-text="m.reference || '-'"></td>
                    {{-- Authorized User Metadata Structure Layout --}}
                    <td class="text-center py-3 text-xs">
                        <div class="">
                            <p class="font-medium text-gray-800 dark:text-zinc-300" x-text="m.user?.name || '-'">
                            </p>
                            <p class="text-gray-400 dark:text-zinc-500 scale-95 origin-left mt-0.5"
                                x-text="m.user?.email || '-'">
                            </p>
                        </div>
                    </td>
                    <td class="pr-2 w-[270px] max-w-[270px] text-xs text-gray-800 dark:text-zinc-300">
                        <div class="flex justify-end">
                            <div class="text-left">
                                <p class="line-clamp-2 break-words" :title="m.notes" x-text="m.notes || '-'"></p>
                            </div>
                        </div>
                    </td>
                </x-table>

                {{-- Empty state: filter/search removed everything --}}
                <div x-show="movements.length > 0 && filteredMovements.length === 0">
                    <div
                        class="text-center py-16 bg-white dark:bg-zinc-900 max-w-xs mx-auto flex flex-col items-center justify-center">
                        <div
                            class="w-12 h-12 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                            <x-heroicon-o-currency-dollar class="w-6 h-6 text-gray-400 dark:text-zinc-500" />
                        </div>
                        {{-- Typography hierarchy --}}
                        <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                            No Results Found
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[200px] leading-relaxed">
                            There are no financial movements matching this filter.
                        </p>
                    </div>
                </div>


                {{-- Empty State Row --}}
                <div x-show="movements.length === 0">
                    <div
                        class="text-center py-16 bg-white dark:bg-zinc-900 max-w-xs mx-auto flex flex-col items-center justify-center">
                        {{-- Circular minimalist movement path icon container --}}
                        <div
                            class="w-12 h-12 rounded-full bg-gray-50 dark:bg-zinc-800/40 border border-gray-150 dark:border-zinc-800 flex items-center justify-center mb-3">
                            <x-heroicon-o-currency-dollar class="w-6 h-6 text-gray-400 dark:text-zinc-500" />
                        </div>

                        {{-- Typography hierarchy --}}
                        <p class="text-xs font-bold text-gray-900 dark:text-zinc-200 uppercase tracking-wider">
                            No Financial Movements
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1 max-w-[200px] leading-relaxed">
                            There are no registered financial transactions for this filter.
                        </p>
                    </div>
                </div>
            </div>

            <x-pagination />
        </div>
    </div>
@endsection
