@extends('layouts.app')

@section('content')
    <div class="p-6 mx-auto space-y-6" x-data="notificationPage()">

        {{-- Header Segment --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-base font-bold tracking-tight text-gray-900 dark:text-zinc-100 uppercase">Notifications</h1>
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Authorize or decline pending system inventory
                    operations</p>
            </div>
        </div>

        @if ($stockRequests->isEmpty())
            {{-- Premium Empty State Segment --}}
            <div
                class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200/80 dark:border-zinc-800 p-16 text-center shadow-sm">
                <div
                    class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center text-gray-400 dark:text-zinc-500">
                    <x-heroicon-o-inbox class="w-6 h-6" />
                </div>
                <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 uppercase tracking-wider">All caught up</p>
                <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">No pending stock requests require
                    authorization.</p>
            </div>
        @else
            @include('admin.notifications.scripts')
            <div class="space-y-8">
                <div id="notificationGroups">
                    @include('admin.notifications.list-messages')
                </div>
            </div>
        @endif
    </div>
@endsection
