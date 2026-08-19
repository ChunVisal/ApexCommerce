<x-sidebar-shell>
    @php
        $links = [
            ['route' => 'cashier.pos', 'icon' => 'o-computer-desktop', 'label' => 'POS'],
            ['route' => 'cashier.orders', 'icon' => 'o-document-duplicate', 'label' => 'Orders'],
            ['route' => 'cashier.products', 'icon' => 'o-cube', 'label' => 'Products'],
            ['route' => 'cashier.customers', 'icon' => 'o-user-group', 'label' => 'Customers'],
        ];
    @endphp

    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ request()->routeIs($link['route']) ? 'bg-blue-50 dark:bg-zinc-900 text-p dark:text-zinc-100' : 'text-gray-700 dark:text-zinc-400 hover:bg-gray-200/30 dark:hover:bg-zinc-900/50' }}"
            :class="open ? '' : 'justify-center'">
            <x-dynamic-component :component="'heroicon-' . $link['icon']" class="w-5 h-5 shrink-0" />
            <span x-show="open" class="text-sm font-medium whitespace-nowrap">{{ $link['label'] }}</span>
        </a>
    @endforeach

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
