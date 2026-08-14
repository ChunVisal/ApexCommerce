{{-- Filters --}}
<div class="flex flex-wrap gap-3 mb-4">
    {{-- Search --}}
    <div class="relative flex-1 min-w-[200px]">
        <i
            class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
        <input type="text" x-model="searchQuery" placeholder="Search activities..."
            class="w-full pl-8 pr-8 py-1.5 text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 border border-gray-200 dark:border-zinc-800 rounded-md focus:outline-none focus:ring-1 focus:ring-p placeholder-gray-400 dark:placeholder-zinc-500">
        <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterActivities()"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
            ✕
        </button>
    </div>

    <div class="relative" class="">
        <select x-model="filterUser"
            class="w-full appearance-none text-xs border border-gray-200 dark:border-zinc-900 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 focus:outline-none focus:border-gray-400 dark:focus:border-zinc-600 transition">
            <option value="">All Users</option>
            @foreach ($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                    {{ $u->name }}</option>
            @endforeach
        </select>
        <x-heroicon-m-chevron-down
            class="w-5 h-5 absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
    </div>
    <div class="relative">
        <select x-model="filterType"
            class="w-[100px] appearance-none text-xs border border-gray-200 dark:border-zinc-900 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 focus:outline-none focus:border-gray-400 dark:focus:border-zinc-600 transition">
            <option value="">All Status</option>
            @foreach ($status as $p)
                <option value="{{ $p }}" {{ request('status') == $p ? 'selected' : '' }}>
                    {{ $p }}
                </option>
            @endforeach
        </select>
        <x-heroicon-m-chevron-down
            class="w-5 h-5 absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
    </div>
    <div class="relative">
        <select x-model="filterPage"
            class="w-full pr-5 appearance-none text-xs border border-gray-200 dark:border-zinc-900 rounded-md px-2 py-1.5 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200 focus:outline-none focus:border-gray-400 dark:focus:border-zinc-600 transition">
            <option value="">All Modules</option>
            @foreach ($pages as $p)
                <option value="{{ $p }}" {{ request('page') == $p ? 'selected' : '' }}>
                    {{ $p }}
                </option>
            @endforeach
        </select>
        <x-heroicon-m-chevron-down
            class="w-5 h-5 absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none" />
    </div>
</div>

{{-- activity List --}}

<div class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-900 rounded-md flex flex-col gap-3 px-5">

    <template x-for="activity in filteredActivities" :key="activity.id">
        <div
            class="border-b border-gray-200/80 dark:border-zinc-700 overflow-hidden flex items-start gap-2 py-5 px-2 dark:border-zinc-900/80 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
            <!-- Avatar -->
            <div class="w-11 h-11 rounded-full flex items-center justify-center text-white text-sm font-semibold shrink-0"
                :style="`background: ${activity.user && activity.user.role === 'admin' ? '#8B5CF6' : '#0F6E8C'}`">
                <template x-if="activity.user_name">
                    <span x-text="activity.user_name.charAt(0).toUpperCase()"></span>
                </template>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800 dark:text-zinc-200">
                    <strong class="text-sm font-medium text-gray-900 dark:text-zinc-100"
                        x-text="activity.user_name"></strong>
                    <span class="text-gray-500 dark:text-zinc-400 text-xs" x-text="activity.description"></span>
                    <span class="text-[14px] rounded-full font-medium"
                        :class="activity.status === 'success' ?
                            'text-green-700 dark:text-green-400' :
                            (activity.status === 'warning' ?
                                'text-amber-700 dark:text-amber-400' :
                                (activity.status === 'danger' ?
                                    'text-red-700 dark:text-red-400' :
                                    'text-p dark:text-cyan-400'))"
                        x-text="activity.page"></span>
                </p>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-[12px] text-gray-500 dark:text-zinc-400"
                        x-text="new Date(activity.created_at).toLocaleString()"></span>
                </div>
            </div>

            <!-- Status Badge -->
            <span class="px-2 py-0.5 text-[9px] rounded-full font-semibold uppercase tracking-wider"
                :class="activity.status === 'success' ?
                    'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400 border border-transparent dark:border-green-800/40' :
                    (activity.status === 'warning' ?
                        'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-transparent dark:border-amber-800/40' :
                        (activity.status === 'danger' ?
                            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-transparent dark:border-red-800/40' :
                            'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-transparent dark:border-blue-800/40'
                        ))"
                x-text="activity.status"></span>
        </div>
    </template>
    <template x-if="filteredActivities.length === 0">
        <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
            {{-- Icon Badge Container --}}
            <div
                class="relative flex items-center justify-center w-12 h-12 rounded-full bg-gray-100/80 dark:bg-zinc-900/60 border border-gray-200/60 dark:border-zinc-700/50 mb-3 shadow-xs">
                <i class="fa-solid fa-magnifying-glass text-gray-400 dark:text-zinc-500 text-lg"></i>
            </div>

            {{-- Typography --}}
            <h3 class="text-xs font-bold text-gray-900 dark:text-zinc-100 uppercase tracking-wider">
                No Activities Found
            </h3>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-zinc-400 max-w-[220px] leading-relaxed">
                Try adjusting your search terms or filter criteria to reveal activity records.
            </p>

            {{-- Quick Reset Button (Optional) --}}
            <button x-show="searchQuery || filterUser || filterPage"
                @click="searchQuery = ''; filterUser = ''; filterPage = ''; filterProducts()" type="button"
                class="mt-4 px-3 py-1 text-[12px] font-medium text-[#0F6E8C] dark:text-cyan-400 hover:underline transition">
                Clear all filters
            </button>
        </div>
    </template>
</div>
