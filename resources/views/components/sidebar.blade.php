<aside x-data="{
    open: localStorage.getItem('sidebar') !== 'closed',
    ready: false,
    toggle(val) {
        this.open = val;
        localStorage.setItem('sidebar', val ? 'open' : 'closed');
        document.documentElement.classList.toggle('sidebar-closed', !val);
    }
}" x-init="$nextTick(() => ready = true)"
    :class="[
        open ? 'w-[200px]' : 'w-14',
        ready ? 'transition-all duration-300' : ''
    ]"
    class="sticky top-[58px] h-[calc(100vh-58px)] flex flex-col bg-white dark:bg-zinc-950 overflow-hidden z-40">

    <div class="absolute right-0 top-0 bottom-0 w-[1px] cursor-ew-resize hover:bg-blue-400 bg-gray-200 dark:bg-zinc-800 transition-all z-50"
        @mousedown.stop="
            let startX = $event.clientX;
            const onMove = (e) => {
                let diff = startX - e.clientX;
                if (diff > 30) toggle(false);
                else if (diff < -30) toggle(true);
            };
            const onUp = () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        ">
    </div>

    <div x-show="open" class="absolute top-2/3 -translate-y-1/2 right-0 z-50 flex items-center justify-center">
        <button @click="toggle(false)"
            class="w-6 h-12 bg-gray-100 dark:bg-zinc-800 border-t border-r border-gray-300 dark:border-zinc-900 rounded-l-lg shadow-sm flex items-center justify-center hover:bg-gray-200/30 dark:hover:bg-zinc-800/50 transition">
            <x-heroicon-o-chevron-left class="w-10 text-gray-600 dark:text-zinc-400" />
        </button>
    </div>

    <nav
        class="overflow-x-hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden flex-1 px-3 py-2 space-y-1 overflow-y-auto">

        <div x-show="!open" class="flex items-center px-3 pb-2 justify-center">
            <button @click="toggle(true)"
                class="flex items-center justify-center hover:bg-gray-200/30 dark:hover:bg-zinc-900/30 transition">
                <x-heroicon-o-chevron-right
                    class="w-7 bg-gray-200/50 dark:bg-zinc-800/50 rounded-sm p-1 text-gray-600 dark:text-zinc-400" />
            </button>
        </div>

        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-squares-2x2 class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Dashboard</span>
        </a>

        {{-- Products with Submenu --}}
        <div x-data="{
            productsOpen: localStorage.getItem('submenu-products') === 'open',
            toggleProductsSubmenu() {
                this.productsOpen = !this.productsOpen;
                localStorage.setItem('submenu-products', this.productsOpen ? 'open' : 'closed');
            }
        }">
            <button @click="open ? toggleProductsSubmenu() : window.location.href = '{{ route('admin.products') }}'"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.products') || request()->routeIs('admin.products.uoms') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
                :class="open ? '' : 'justify-center'">
                <x-heroicon-o-cube class="w-5 h-5 shrink-0" />
                <span x-show="open" class="text-sm font-medium whitespace-nowrap flex-1 text-left">Products</span>
                <x-heroicon-o-chevron-down x-show="open" :class="productsOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" />
            </button>

            {{-- Submenu --}}
            <div x-show="open && productsOpen" x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                x-transition:leave-end="opacity-0 max-h-0 overflow-hidden" class="ml-4 space-y-2 mt-2">
                <a href="{{ route('admin.products') }}"
                    class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors {{ request()->routeIs('admin.products') && !request()->routeIs('admin.products.uoms') ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                    Products List
                </a>
                <a href="{{ route('admin.products.uoms') }}"
                    class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors {{ request()->routeIs('admin.products.uoms') ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                    Product UOMs
                </a>
            </div>
        </div>

        {{-- Inventory with Submenu --}}
        <div x-data="{
            inventoryOpen: localStorage.getItem('submenu-inventory') === 'open',
            toggleSubmenu() {
                this.inventoryOpen = !this.inventoryOpen;
                localStorage.setItem('submenu-inventory', this.inventoryOpen ? 'open' : 'closed');
            }
        }">
            <button @click="open ? toggleSubmenu() : window.location.href = '{{ route('admin.inventory') }}'"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.inventory*') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
                :class="open ? '' : 'justify-center'">
                <x-heroicon-o-archive-box class="w-5 h-5 shrink-0" />
                <span x-show="open" class="text-sm font-medium whitespace-nowrap flex-1 text-left">Inventory</span>
                <x-heroicon-o-chevron-down x-show="open" :class="inventoryOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" />
            </button>

            {{-- Submenu --}}
            <div x-show="open && inventoryOpen" x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                x-transition:leave-end="opacity-0 max-h-0 overflow-hidden" class="ml-4 space-y-2 mt-2">
                <a href="{{ route('admin.inventory') }}"
                    class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors {{ request()->routeIs('admin.inventory') && !request()->routeIs('admin.inventory.movements') ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                    Stock Overview
                </a>
                <a href="{{ route('admin.inventory.movements') }}"
                    class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors {{ request()->routeIs('admin.inventory.movements') ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                    Stock Movements
                </a>
            </div>
        </div>

        {{-- Users with Submenu --}}
        <div x-data="{
            usersOpen: localStorage.getItem('submenu-users') === 'open',
            toggleUsersSubmenu() {
                this.usersOpen = !this.usersOpen;
                localStorage.setItem('submenu-users', this.usersOpen ? 'open' : 'closed');
            }
        }">
            <button @click="open ? toggleUsersSubmenu() : window.location.href = '{{ route('admin.users') }}'"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.users') || request()->routeIs('admin.staff') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
                :class="open ? '' : 'justify-center'">
                <x-heroicon-o-user class="w-5 h-5 shrink-0" />
                <span x-show="open" class="text-sm font-medium whitespace-nowrap flex-1 text-left">Users</span>
                <x-heroicon-o-chevron-down x-show="open" :class="usersOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" />
            </button>
            <div x-show="open && usersOpen" x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                x-transition:leave-end="opacity-0 max-h-0 overflow-hidden" class="ml-4 space-y-2 mt-2">
                <a href="{{ route('admin.users') }}"
                    class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors {{ request()->routeIs('admin.users') ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                    Users List
                </a>
                <a {{-- href="{{ route('admin.staff') }}" --}}
                    class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors {{ request()->routeIs('admin.staff') ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                    Staff
                </a>
            </div>
        </div>

        <a href="{{ route('admin.customers') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.customers') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-user-group class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Customers</span>
        </a>

        <a href="{{ route('admin.reports') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.reports') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-chart-bar class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Reports</span>
        </a>

        <a href="{{ route('admin.activitylog') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.activitylog') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-clipboard-document-list class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Activity Log</span>
        </a>

        <a href="{{ route('admin.settings') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('admin.settings') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Settings</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-200/30 dark:hover:bg-zinc-900/50 text-red-700 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 w-full transition-all"
                :class="open ? '' : 'justify-center'">
                <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 shrink-0" />
                <span x-show="open" class="text-sm font-medium whitespace-nowrap">Logout</span>
            </button>
        </form>
    </nav>
</aside>
