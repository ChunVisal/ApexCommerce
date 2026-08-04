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
        open ? 'w-[160px]' : 'w-14',
        ready ? 'transition-all duration-300' : ''
    ]"
    class="sticky top-[58px] h-[calc(100vh-58px)] flex flex-col bg-white dark:bg-black overflow-hidden z-40">

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

    <div x-show="open" class="absolute top-1/3 -translate-y-1/2 right-0 z-50 flex items-center justify-center">
        <button @click="toggle(false)"
            class="w-6 h-12 bg-gray-100 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-800 rounded-l-lg shadow-sm flex items-center justify-center hover:bg-gray-200/30 dark:hover:bg-zinc-800/50 transition">
            <x-heroicon-o-chevron-left class="w-10 text-gray-600 dark:text-zinc-400" />
        </button>
    </div>

    <nav class="tab-container overflow-x-hidden flex-1 px-3 py-2 space-y-1 overflow-y-auto">

        <div x-show="!open" class="flex items-center px-3 pb-2 justify-center">
            <button @click="toggle(true)"
                class="flex items-center justify-center hover:bg-gray-200/30 dark:hover:bg-zinc-900/30 transition">
                <x-heroicon-o-chevron-right
                    class="w-7 bg-gray-200/50 dark:bg-zinc-800/50 rounded-sm p-1 text-gray-600 dark:text-zinc-400" />
            </button>
        </div>

        <a href="{{ route('cashier.pos') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('cashier.pos') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-computer-desktop class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">POS</span>
        </a>

        <a href="{{ route('cashier.orders') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('cashier.orders') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-document-duplicate class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Orders</span>
        </a>

        <a href="{{ route('cashier.products') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('cashier.products') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-cube class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Products</span>
        </a>

        <a href="{{ route('cashier.customers') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs('cashier.customers') ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-user-group class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Customers</span>
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
