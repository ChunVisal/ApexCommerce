@extends('layouts.cashier')

@section('content')
    <div x-data="notificationPage()" class="p-6 mx-auto space-y-6">

        {{-- Header & Days Filter Segment --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-base font-bold tracking-tight text-gray-900 dark:text-zinc-100 uppercase">Notifications</h1>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Review and monitor status updates for your stock
                    requests</p>
            </div>
        </div>

        @if ($notifications->isEmpty())
            {{-- Empty State Layout --}}
            <div
                class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200/80 dark:border-zinc-800 p-16 text-center shadow-sm">
                <div
                    class="w-14 h-14 mx-auto mb-3 bg-gray-50 dark:bg-zinc-800/50 border border-gray-100 dark:border-zinc-800 rounded-full flex items-center justify-center text-gray-400 dark:text-zinc-500">
                    <x-heroicon-o-bell-slash class="w-6 h-6 text-gray-600 dark:text-zinc-300" />
                </div>
                <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 uppercase tracking-wider">No notifications yet
                </p>
                <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">We'll alert you here when your stock requests
                    change status.</p>
            </div>
        @else
            @php
                // Fallback grouping logic if $notifications isn't already grouped from controller
$groupedNotifications =
    is_array($notifications->first()) ||
    $notifications->first() instanceof \Illuminate\Support\Collection
        ? $notifications
        : $notifications->groupBy(function ($item) {
            $date = $item->created_at;
            if ($date->isToday()) {
                return 'Today';
            }
            if ($date->isYesterday()) {
                return 'Yesterday';
            }
            if ($date->greaterThanOrEqualTo(now()->subDays(7))) {
                return 'This Week';
            }
            return 'Older';
                        });
            @endphp

            @include('cashier.notifications.scripts')
            @include('cashier.notifications.return-stock-slideover')

            <div class="space-y-8">
                <div id="notificationGroups">
                    @include('cashier.notifications.list-messages')
                </div>
            </div>
        @endif
    </div>
@endsection
