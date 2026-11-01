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

        <x-table headers="financialHeader">
            <td class=" text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap"
                x-text="new Date(row.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })">
            </td>
            <td class=" ">
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block"
                    :class="row.type === 'in' ?
                        'bg-green-50 dark:bg-green-950/30 text-green-600 dark:text-green-400' :
                        'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400'"
                    x-text="row.type === 'in' ? 'IN' : 'OUT'"></span>
            </td>
            <td class=" text-xs capitalize text-gray-700 dark:text-zinc-300" x-text="row.category"></td>
            <td class=" text-gray-700 dark:text-zinc-300 font-semibold whitespace-nowrap"
                x-text="row.net_amount != null ? '$' + row.net_amount : '-'"></td>
            <td class="  font-semibold whitespace-nowrap"
                :class="row.type === 'in' ? 'text-green-600 dark:text-green-400' :
                    'text-red-600 dark:text-red-400'"
                x-text="(row.type === 'in' ? '+$' : '-$') + row.amount"></td>
            <td class=" text-xs text-gray-800 dark:text-zinc-300 whitespace-nowrap" x-text="row.reference || '-'">
            </td>
            {{-- Authorized User Metadata Structure Layout --}}
            <td class=" py-3 text-xs">
                <div class="inline-block text-left">
                    <p class="font-medium text-gray-800 dark:text-zinc-300" x-text="row.user?.name || '-'">
                    </p>
                    <p class="text-gray-400 dark:text-zinc-500 scale-95 origin-left mt-0.5" x-text="row.user?.email || '-'">
                    </p>
                </div>
            </td>

            <td class="pr-2 w-[270px] max-w-[270px] text-xs text-gray-800 dark:text-zinc-300">
                <div class="flex justify-end">
                    <div class="text-left">
                        <p class="whitespace-normal line-clamp-2 break-words" :title="row.notes"
                            x-text="row.notes || '-'"></p>
                    </div>
                </div>
            </td>

            <x-slot:empty>
                {{-- Empty state: filter/search removed everything --}}
                <x-empty-state icon="heroicon-o-currency-dollar"
                    x-show="movements.length > 0 && filteredRecords.length === 0" title="No results found"
                    description="There are no financial movements matching this filter." />

                {{-- Empty state: no financial movements yet --}}
                <x-empty-state icon="heroicon-o-currency-dollar" x-show="movements.length === 0"
                    title="No financial movements" description="There are no registered financial transactions yet." />

            </x-slot:empty>
        </x-table>
    </div>
@endsection
