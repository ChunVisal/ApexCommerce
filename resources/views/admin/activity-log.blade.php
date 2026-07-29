@extends('layouts.app')

@section('content')
    <div class="p-5">
        <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100 mb-4">Activity Log</h1>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 mb-4">
            <select onchange="window.location.href='?user_id='+this.value"
                class="text-xs border border-gray-200 dark:border-zinc-800 rounded-md px-3 py-1.5 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 focus:outline-none focus:border-gray-400 dark:focus:border-zinc-600 transition">
                <option value="">All Users</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}</option>
                @endforeach
            </select>
            <select onchange="window.location.href='?page='+this.value"
                class="text-xs border border-gray-200 dark:border-zinc-800 rounded-md px-3 py-1.5 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 focus:outline-none focus:border-gray-400 dark:focus:border-zinc-600 transition">
                <option value="">All Pages</option>
                @foreach ($pages as $p)
                    <option value="{{ $p }}" {{ request('page') == $p ? 'selected' : '' }}>{{ $p }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Log List --}}
        <div
            class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800 overflow-hidden">
            @foreach ($logs as $log)
                <div
                    class="flex items-start gap-3 p-5 border-b border-gray-200 dark:border-zinc-800/80 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-semibold shrink-0"
                        style="background: {{ $log->user->role === 'admin' ? '#8B5CF6' : '#0F6E8C' }}">
                        {{ strtoupper(substr($log->user_name, 0, 1)) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 dark:text-zinc-200">
                            <strong
                                class="text-sm font-medium text-gray-900 dark:text-zinc-100">{{ $log->user_name }}</strong>
                            <span class="text-gray-500 dark:text-zinc-400 text-xs"> {{ $log->description }}</span>
                            <span
                                class="text-xs rounded-full font-medium
                        {{ $log->status === 'success'
                            ? 'text-green-700 dark:text-green-400'
                            : ($log->status === 'warning'
                                ? 'text-amber-700 dark:text-amber-400'
                                : ($log->status === 'danger'
                                    ? 'text-red-700 dark:text-red-400'
                                    : 'text-p dark:text-cyan-400')) }}">
                                {{ $log->page }}
                            </span>
                        </p>
                        <div class="flex items-center gap-3 mt-1">
                            <span
                                class="text-[12px] text-gray-400 dark:text-zinc-500">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <span
                        class="px-2 py-0.5 text-[9px] rounded-full font-semibold uppercase tracking-wider
                {{ $log->status === 'success'
                    ? 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400 border border-transparent dark:border-green-800/40'
                    : ($log->status === 'warning'
                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-transparent dark:border-amber-800/40'
                        : ($log->status === 'danger'
                            ? 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-transparent dark:border-red-800/40'
                            : 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-transparent dark:border-blue-800/40')) }}">
                        {{ $log->status }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
@endsection
