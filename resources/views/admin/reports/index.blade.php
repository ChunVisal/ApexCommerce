@extends('layouts.app')


@section('content')
    @include('admin.reports.scripts')
    <div x-data="reportsPage()" class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300">
        {{-- <x-skeleton.reports> --}}
        @include('admin.reports.header-cards')
        {{-- Tabs --}}
        <div class="space-y-4">
            <!-- Tab Controls Bar -->
            <div class="border-b border-gray-200 dark:border-zinc-800">
                <nav class="flex space-x-6 overflow-x-auto no-scrollbar" aria-label="Tabs">
                    <!-- Daily Sales -->
                    <button @click="setTab('daily')"
                        :class="tab === 'daily'
                            ?
                            'border-[#0F6E8C] text-[#0F6E8C] dark:text-[#1898be] font-semibold' :
                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:border-gray-300 dark:hover:border-zinc-700 font-medium'"
                        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs transition-all duration-200 ease-in-out whitespace-nowrap ">
                        <i class="fa-solid fa-chart-column w-4 h-4"></i>
                        Daily Sales
                    </button>

                    <!-- Top Cashiers -->
                    <button @click="setTab('cashiers')"
                        :class="tab === 'cashiers'
                            ?
                            'border-[#0F6E8C] text-[#0F6E8C] dark:text-[#1898be] font-semibold' :
                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:border-gray-300 dark:hover:border-zinc-700 font-medium'"
                        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs transition-all duration-200 ease-in-out whitespace-nowrap ">
                        <i class="fa-solid fa-users w-4 h-4"></i>
                        Top Cashiers
                    </button>

                    <!-- Payments -->
                    <button @click="setTab('payments')"
                        :class="tab === 'payments'
                            ?
                            'border-[#0F6E8C] text-[#0F6E8C] dark:text-[#1898be] font-semibold' :
                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:border-gray-300 dark:hover:border-zinc-700 font-medium'"
                        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs transition-all duration-200 ease-in-out whitespace-nowrap ">
                        <i class="fa-solid fa-credit-card w-4 h-4"></i>
                        Payments
                    </button>

                    <!-- Orders -->
                    <button @click="setTab('orders')"
                        :class="tab === 'orders'
                            ?
                            'border-[#0F6E8C] text-[#0F6E8C] dark:text-[#1898be] font-semibold' :
                            'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:border-gray-300 dark:hover:border-zinc-700 font-medium'"
                        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs transition-all duration-200 ease-in-out whitespace-nowrap ">
                        <i class="fa-solid fa-receipt w-4 h-4"></i>
                        Orders
                    </button>
                </nav>
            </div>

            <!-- Tab Panels -->
            <div>
                <div x-show="tab === 'daily'" x-cloak x-transition.opacity.duration.150ms>
                    @include('admin.reports.daily-sales')
                </div>
                <div x-show="tab === 'cashiers'" x-cloak x-cloak x-transition.opacity.duration.150ms>
                    @include('admin.reports.top-cashiers')
                </div>
                <div x-show="tab === 'payments'" x-cloak x-cloak x-transition.opacity.duration.150ms>
                    @include('admin.reports.payments-breakdown')
                </div>
                <div x-show="tab === 'orders'" x-cloak x-cloak x-transition.opacity.duration.150ms>
                    @include('admin.reports.total-orders')
                </div>
            </div>
        </div>
    </div>
@endsection
