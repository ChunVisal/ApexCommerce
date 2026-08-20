@props(['role' => 'cashier'])

<nav
    class="bg-white dark:bg-black border-b border-gray-300 dark:border-zinc-800 px-4 py-1 flex items-center justify-between sticky top-0 z-40 transition-colors duration-200">

    <a href="{{ $role === 'admin' ? route('admin.dashboard') : route('cashier.pos') }}" class="flex items-center">
        @php $logo = App\Models\Setting::get('logo'); @endphp
        @if ($logo)
            <img src="{{ $logo }}" class="h-[45px] w-auto max-w-full object-contain shrink-0" />
        @else
            <img src="{{ asset('images/logo.png') }}"
                class="h-[41px] w-auto max-w-[120px] object-contain shrink-0 dark:hidden" />
            <img src="{{ asset('images/logodarkmode.png') }}"
                class="h-[41px] w-auto max-w-[120px] object-contain shrink-0 hidden dark:block" />
        @endif
    </a>

    <div class="flex items-center gap-3">
        <x-dark-mode-toggle />

        <x-notification-bell :role="$role" />

        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold overflow-hidden"
                style="background-color: {{ auth()->user()->role === 'admin' ? '#8B5CF6' : '#0F6E8C' }};">
                @if (auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                @else
                    <span class="text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
            <div class="hidden sm:block leading-none">
                <p class="font-semibold text-sm text-gray-800 dark:text-zinc-200">{{ auth()->user()->name }}
                </p>
                <span class="text-xs text-[#0F6E8C] dark:text-[#138cb3] font-medium">{{ auth()->user()->role }}</span>
            </div>
        </div>
    </div>
</nav>
