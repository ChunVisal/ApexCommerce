<x-sidebar-shell>

    {{-- Dashboard --}}
    <a href="{{ route('admin.dashboard') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('admin.dashboard')
            ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
            : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
        :class="open ? '' : 'justify-center'">

        <x-heroicon-o-squares-2x2 class="w-5 h-5 shrink-0" />

        <span x-show="open" class="text-sm font-medium whitespace-nowrap">
            Dashboard
        </span>
    </a>


    {{-- Products --}}
    <div x-data="{
        openSub: localStorage.getItem('submenu-products') === 'open',
    
        toggle() {
            this.openSub = !this.openSub
            localStorage.setItem(
                'submenu-products',
                this.openSub ? 'open' : 'closed'
            )
        }
    }">

        <button @click="open ? toggle() : window.location.href = '{{ route('admin.products') }}'"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
            {{ request()->routeIs('admin.products', 'admin.products.uoms')
                ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
                : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">

            <x-heroicon-o-cube class="w-5 h-5 shrink-0" />

            <span x-show="open" class="text-sm font-medium whitespace-nowrap flex-1 text-left">
                Products
            </span>

            <x-heroicon-o-chevron-down x-show="open" :class="openSub ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" />
        </button>


        {{-- Products submenu --}}
        <div x-show="open && openSub" x-transition class="ml-4 space-y-2 mt-2">

            <a href="{{ route('admin.products') }}"
                class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors
                {{ request()->routeIs('admin.products')
                    ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100'
                    : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                Products List
            </a>

            <a href="{{ route('admin.products.uoms') }}"
                class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors
                {{ request()->routeIs('admin.products.uoms')
                    ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100'
                    : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                Product UOMs
            </a>

        </div>
    </div>


    {{-- Inventory --}}
    <div x-data="{
        openSub: localStorage.getItem('submenu-inventory') === 'open',
    
        toggle() {
            this.openSub = !this.openSub
            localStorage.setItem(
                'submenu-inventory',
                this.openSub ? 'open' : 'closed'
            )
        }
    }">

        <div x-data="movementBadge()" x-init="init()">
            <button @click="open ? toggle() : window.location.href = '{{ route('admin.inventory') }}'"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                {{ request()->routeIs('admin.inventory*')
                    ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
                    : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
                :class="open ? '' : 'justify-center'">
                <div class="relative shrink-0">
                    <x-heroicon-o-archive-box class="w-5 h-5" />
                    <span x-show="!open && count > 0"
                        class="absolute -top-[10px] right-3 bg-red-500 text-white text-[9px] font-medium rounded-full px-1.5 py-[1px]"
                        x-text="count >= 100 ? '99+' : count">
                    </span>
                </div>
                <span x-show="open" class="text-sm font-medium whitespace-nowrap flex-1 text-left">
                    Inventory
                </span>
                <x-heroicon-o-chevron-down x-show="open" :class="openSub ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" />
            </button>
            <div>
                {{-- Inventory submenu --}}
                <template x-if="open && openSub">
                    <div x-transition class="ml-4 space-y-2 mt-2">
                        <a href="{{ route('admin.inventory') }}"
                            class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors
                            {{ request()->routeIs('admin.inventory')
                                ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100'
                                : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                            Stock Overview
                        </a>
                        <a href="{{ route('admin.inventory.movements') }}"
                            class="relative block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors
                                {{ request()->routeIs('admin.inventory.movements')
                                    ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100'
                                    : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                            <span x-show="count > 0"
                                class="absolute -top-1 left-1 bg-red-500 text-white text-[10px] font-bold rounded-full px-1.5"
                                x-text="count >= 100 ? '99+' : count">
                            </span>
                            Stock Movements
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Users --}}
    <a href="{{ route('admin.users') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('admin.users')
            ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
            : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
        :class="open ? '' : 'justify-center'">

        <x-heroicon-o-user class="w-5 h-5 shrink-0" />

        <span x-show="open" class="text-sm font-medium whitespace-nowrap">
            Users
        </span>
    </a>


    {{-- Customers --}}
    <a href="{{ route('admin.customers') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('admin.customers')
            ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
            : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
        :class="open ? '' : 'justify-center'">

        <x-heroicon-o-user-group class="w-5 h-5 shrink-0" />

        <span x-show="open" class="text-sm font-medium whitespace-nowrap">
            Customers
        </span>
    </a>

    {{-- Reports --}}
    <a href="{{ route('admin.reports') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('admin.reports')
            ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
            : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
        :class="open ? '' : 'justify-center'">

        <x-heroicon-o-chart-bar class="w-5 h-5 shrink-0" />

        <span x-show="open" class="text-sm font-medium whitespace-nowrap">
            Reports
        </span>
    </a>


    {{-- Activity Log --}}
    <a href="{{ route('admin.activitylog') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('admin.activitylog')
            ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
            : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
        :class="open ? '' : 'justify-center'">

        <x-heroicon-o-clipboard-document-list class="w-5 h-5 shrink-0" />

        <span x-show="open" class="text-sm font-medium whitespace-nowrap">
            Activity Log
        </span>
    </a>


    {{-- Settings --}}
    <a href="{{ route('admin.settings') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('admin.settings')
            ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100'
            : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
        :class="open ? '' : 'justify-center'">

        <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0" />

        <span x-show="open" class="text-sm font-medium whitespace-nowrap">
            Settings
        </span>
    </a>


    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg
            hover:bg-gray-200/30 dark:hover:bg-zinc-900/50
            text-red-700 dark:text-red-400
            hover:text-red-900 dark:hover:text-red-300
            w-full transition-all"
            :class="open ? '' : 'justify-center'">

            <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 shrink-0" />

            <span x-show="open" class="text-sm font-medium whitespace-nowrap">
                Logout
            </span>
        </button>
    </form>

</x-sidebar-shell>
