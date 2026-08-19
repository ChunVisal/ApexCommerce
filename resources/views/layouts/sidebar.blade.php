<x-sidebar-shell>
    @php
        $navItems = [
            [
                'route' => 'admin.dashboard',
                'icon' => 'o-squares-2x2',
                'label' => 'Dashboard',
                'type' => 'link',
            ],
            [
                'route' => 'admin.products',
                'icon' => 'o-cube',
                'label' => 'Products',
                'type' => 'parent',
                'key' => 'products',
                'children' => [
                    ['route' => 'admin.products', 'label' => 'Products List', 'exclude' => ['admin.products.uoms']],
                    ['route' => 'admin.products.uoms', 'label' => 'Product UOMs'],
                ],
                'active' => ['admin.products', 'admin.products.uoms'],
            ],
            [
                'route' => 'admin.inventory',
                'icon' => 'o-archive-box',
                'label' => 'Inventory',
                'type' => 'parent',
                'key' => 'inventory',
                'children' => [
                    [
                        'route' => 'admin.inventory',
                        'label' => 'Stock Overview',
                        'exclude' => ['admin.inventory.movements'],
                    ],
                    ['route' => 'admin.inventory.movements', 'label' => 'Stock Movements'],
                ],
                'active' => ['admin.inventory*'],
            ],
            [
                'route' => 'admin.users',
                'icon' => 'o-user',
                'label' => 'Users',
                'type' => 'parent',
                'key' => 'users',
                'children' => [
                    ['route' => 'admin.users', 'label' => 'Users List'],
                    ['route' => 'admin.users', 'label' => 'Staff'],
                ],
                'active' => ['admin.users', 'admin.staff'],
            ],
            ['route' => 'admin.customers', 'icon' => 'o-user-group', 'label' => 'Customers', 'type' => 'link'],
            ['route' => 'admin.reports', 'icon' => 'o-chart-bar', 'label' => 'Reports', 'type' => 'link'],
            [
                'route' => 'admin.activitylog',
                'icon' => 'o-clipboard-document-list',
                'label' => 'Activity Log',
                'type' => 'link',
            ],
            ['route' => 'admin.settings', 'icon' => 'o-cog-6-tooth', 'label' => 'Settings', 'type' => 'link'],
        ];
    @endphp

    @foreach ($navItems as $item)
        @if ($item['type'] === 'link')
            {{-- Simple Link --}}
            <a href="{{ route($item['route']) }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs($item['route']) ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
                :class="open ? '' : 'justify-center'">
                <x-dynamic-component :component="'heroicon-' . $item['icon']" class="w-5 h-5 shrink-0" />
                <span x-show="open" class="text-sm font-medium whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
        @else
            {{-- Parent with Submenu --}}
            <div x-data="{
                openSub: localStorage.getItem('submenu-{{ $item['key'] }}') === 'open',
                toggle() {
                    this.openSub = !this.openSub;
                    localStorage.setItem('submenu-{{ $item['key'] }}', this.openSub ? 'open' : 'closed');
                }
            }">
                <button @click="open ? toggle() : window.location.href = '{{ route($item['route']) }}'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs($item['active']) ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
                    :class="open ? '' : 'justify-center'">
                    <x-dynamic-component :component="'heroicon-' . $item['icon']" class="w-5 h-5 shrink-0" />
                    <span x-show="open"
                        class="text-sm font-medium whitespace-nowrap flex-1 text-left">{{ $item['label'] }}</span>
                    <x-heroicon-o-chevron-down x-show="open" :class="openSub ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" />
                </button>

                <div x-show="open && openSub" x-transition:enter="transition-all ease-out duration-300"
                    x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                    x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                    x-transition:leave="transition-all ease-in duration-200"
                    x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                    x-transition:leave-end="opacity-0 max-h-0 overflow-hidden" class="ml-4 space-y-2 mt-2">
                    @foreach ($item['children'] as $child)
                        <a href="{{ route($child['route']) }}"
                            class="block px-3 py-2 text-xs font-medium rounded-lg whitespace-nowrap transition-colors {{ request()->routeIs($child['route']) && !request()->routeIs($child['exclude'] ?? []) ? 'bg-blue-50 dark:bg-zinc-800/70 text-p dark:text-zinc-100' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                            {{ $child['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-200/30 dark:hover:bg-zinc-900/50 text-red-700 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 w-full transition-all"
            :class="open ? '' : 'justify-center'">
            <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">Logout</span>
        </button>
    </form>
</x-sidebar-shell>
