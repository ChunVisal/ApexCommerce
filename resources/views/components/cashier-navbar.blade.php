<nav
    class="bg-white dark:bg-black border-b border-gray-300 dark:border-zinc-800 px-4 py-2 flex items-center justify-between sticky top-0 z-40 transition-colors duration-200">
    <!-- Logo Section -->
    <div class="flex items-center">
        {{-- <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-[90px] dark:hidden shrink-0">
        <img src="{{ asset('images/logodarkmode.png') }}" alt="Logo" class="w-[90px] hidden dark:block shrink-0"> --}}

        @php $logo = App\Models\Setting::get('logo'); @endphp

        @if ($logo)
            <img src="{{ $logo }}" class="h-[41px] w-auto max-w-[120px] object-contain shrink-0" />
        @else
            <img src="{{ asset('images/logo.png') }}"
                class="h-[41px] w-auto max-w-[120px] object-contain shrink-0 dark:hidden" />
            <img src="{{ asset('images/logodarkmode.png') }}"
                class="h-[41px] w-auto max-w-[120px] object-contain shrink-0 hidden dark:block" />
        @endif
    </div>

    <div class="flex items-center gap-3">

        <x-dark-mode-toggle />

        {{-- Cashier Bell Icon with Dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                class="relative p-2 text-gray-500 dark:text-zinc-400 hover:text-[#0F6E8C] dark:hover:text-[#138cb3] hover:bg-gray-100 dark:hover:bg-zinc-900 rounded-full transition-colors"
                title="Notifications">
                {{-- Use correct FontAwesome bell icon (solid style) --}}
                <i class="fa-solid fa-bell text-xl text-gray-700 dark:text-gray-200"></i>
                @php
                    $cashierNotifCount = \App\Models\StockRequest::where('cashier_id', auth()->id())
                        ->where('created_at', '>=', now()->subDays(15))
                        ->whereIn('status', ['pending', 'approved', 'rejected', 'on_hold'])
                        ->whereNull('seen_at')
                        ->count();
                @endphp
                @if ($cashierNotifCount > 0)
                    <span
                        class="bell-badge absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $cashierNotifCount }}
                    </span>
                @endif
            </button>

            <div x-show="open" @click.outside="open = false" x- cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-xl dark:shadow-zinc-950/50 z-50 overflow-hidden">

                {{-- Premium Minimal Header Segment Layout --}}
                <div
                    class="px-4 py-3 bg-gray-50/50 dark:bg-zinc-900/40 border-b border-gray-100 dark:border-zinc-800/60 flex items-center justify-between">
                    {{-- Left Side: Header Title --}}
                    <h3 class="text-md font-bold text-gray-900 dark:text-zinc-100">
                        Notifications</h3>

                    {{-- Right Side: Action Controllers --}}
                    <div class="flex items-center gap-3">
                        {{-- Mark all as read Button UI --}}
                        <button type="button" @click="markAllRead()"
                            class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1 text-[#0F6E8C] dark:text-[#1389af] hover:text-cyan-700 dark:hover:text-cyan-400 transition-colors"
                            title="Mark all notifications as read">
                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                            <span>Mark read</span>
                        </button>

                        {{-- Vertical Minimal Divider --}}
                        <span class="w-[1px] h-3 bg-gray-200 dark:bg-zinc-800"></span>

                        {{-- View All Route Action --}}
                        <a href="{{ route('cashier.notifications') }}"
                            class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100 hover:underline transition-colors">
                            View All
                        </a>
                    </div>
                </div>
                {{-- Notification List Feed Container Block --}}
                <div
                    class="max-h-[320px] tab-container overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800/40">
                    @php
                        $pendingCashierNotifs = \App\Models\StockRequest::with(['product', 'approver'])
                            ->where('cashier_id', auth()->id())
                            ->where('created_at', '>=', now()->subDays(30))
                            ->whereIn('status', ['pending', 'approved', 'rejected', 'on_hold'])
                            ->latest()
                            ->limit(6)
                            ->get();

                        $sortedNotifs = $pendingCashierNotifs->sortBy(function ($notif) {
                            // pending = 0, recently approved/rejected = 1, older = 2
                            if ($notif->status === 'pending') {
                                return 0;
                            }
                            if (
                                in_array($notif->status, ['approved', 'rejected']) &&
                                $notif->updated_at->diffInMinutes(now()) < 60
                            ) {
                                return 1;
                            }
                            return 2;
                        });
                    @endphp

                    @forelse($sortedNotifs as $notif)
                        <div class="notif-card flex items-start gap-3 px-4 py-3 transition-colors
                    {{ empty($notif->seen_at) ? 'bg-blue-50 dark:bg-blue-950/20 border-l-2 border-blue-500' : 'hover:bg-gray-50/60 dark:hover:bg-zinc-800/30' }}"
                            data-notif-id="{{ $notif->id }}" style="cursor:pointer"
                            @click.stop="markSingleRead({{ $notif->id }}, $event.target)">
                            <div class="relative flex-shrink-0">
                                <div
                                    class="w-11 h-11 rounded-sm bg-gray-100 dark:bg-zinc-850 border border-gray-200/60 dark:border-zinc-800 overflow-hidden flex items-center justify-center">
                                    @if (!empty($notif->product->image))
                                        <img src="{{ $notif->product->image }}" alt="{{ $notif->product->name }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <img src="https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png"
                                            alt="Placeholder" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div
                                    class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900
            {{ $notif->status === 'approved' ? 'bg-green-500' : ($notif->status === 'rejected' ? 'bg-red-500' : 'bg-amber-500') }}">
                                    @if ($notif->status === 'approved')
                                        <x-heroicon-s-check class="w-2.5 h-2.5 text-white" />
                                    @elseif($notif->status === 'rejected')
                                        <x-heroicon-s-x-mark class="w-2.5 h-2.5 text-white" />
                                    @else
                                        <x-heroicon-s-clock class="w-2.5 h-2.5 text-white" />
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 space-y-0.5">
                                <p class="text-xs text-gray-800 dark:text-zinc-200 leading-snug break-words">
                                    @if ($notif->quantity_approved)
                                        <span class="font-extrabold">({{ $notif->quantity_approved }} sent)</span>
                                        <span class="font-medium">{{ $notif->quantity_requested }}x</span>
                                    @else
                                        <span class="font-bold">{{ $notif->quantity_requested }}x</span>
                                    @endif
                                    <span
                                        class="font-medium">{{ $notif->product->name ?? ($notif->product_name ?? 'Unknown') }}
                                        @if ($notif->product && $notif->product->base_unit_name)
                                            <span class="text-gray-700 dark:text-zinc-400">
                                                ({{ $notif->product->base_unit_name }})
                                            </span>
                                        @endif
                                    </span>
                                </p>
                                <p
                                    class="text-xs font-normal tracking-normal mt-1
            {{ $notif->status === 'approved' ? 'text-green-600' : ($notif->status === 'rejected' ? 'text-red-600' : 'text-amber-600') }}">
                                    {{ $notif->status === 'approved' ? 'Approved' : ($notif->status === 'rejected' ? 'Rejected' : ($notif->status === 'on_hold' ? 'On Hold' : 'Pending')) }}
                                </p>
                                <p class="text-[11px] text-gray-400 dark:text-zinc-500 font-medium pt-0.5">
                                    {{ $notif->updated_at->diffForHumans() }}
                                </p>
                            </div>
                            <button @click.stop="markSingleRead({{ $notif->id }}, $event.target)"
                                class="notif-dot ml-2 flex items-center justify-center w-2.5 h-2.5 rounded-full
                                    {{ $notif->seen_at ? 'bg-gray-300 dark:bg-zinc-700' : 'bg-red-500' }}"
                                title="Mark as read" aria-label="Mark as read" type="button">
                            </button>
                        </div>

                    @empty
                        <div class="px-4 py-12 text-center text-xs font-medium text-gray-400 dark:text-zinc-500">
                            <x-heroicon-o-bell-slash class="w-6 h-6 mx-auto mb-2 opacity-60" />
                            No notifications
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

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
                <p class="font-semibold text-sm text-gray-800 dark:text-zinc-200">{{ auth()->user()->name ?? 'Guest' }}
                </p>
                <span
                    class="text-xs text-[#0F6E8C] dark:text-[#138cb3] font-medium">{{ auth()->user()->role ?? 'User' }}</span>
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
        const sidebar = document.querySelector('aside');
        if (sidebar && sidebar.__x) {
            sidebar.__x.$data.open = !sidebar.__x.$data.open;
        }
    });

    function markAllRead() {
        fetch('/cashier/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // gray out every dot
                document.querySelectorAll('.notif-dot').forEach(dot => {
                    dot.classList.remove('bg-red-500', 'bg-blue-500');
                    dot.classList.add('bg-gray-300', 'dark:bg-zinc-700');
                });
                // remove both mark and unmark background classes on notif cards
                document.querySelectorAll('.notif-card').forEach(card => {
                    card.classList.remove(
                        'bg-red-50',
                        'dark:bg-zinc-900/30',
                        'bg-blue-50',
                        'dark:bg-blue-950/20',
                        'border-l-2',
                        'border-blue-500'
                    );
                });

                // clear the bell badge count
                const badge = document.querySelector('.bell-badge');
                if (badge) badge.remove();
            });
    }

    function markSingleRead(id, el) {
        fetch(`/cashier/notifications/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                let notifCard, notifDot;

                if (el.classList.contains('notif-dot')) {
                    notifDot = el;
                    notifCard = el.closest('.notif-card');
                } else if (el.classList.contains('notif-card')) {
                    notifCard = el;
                    notifDot = notifCard.querySelector('.notif-dot');
                } else {
                    notifCard = document.querySelector(`.notif-card[data-notif-id="${id}"]`);
                    notifDot = notifCard ? notifCard.querySelector('.notif-dot') : null;
                }

                if (notifDot) {
                    notifDot.classList.remove('bg-blue-500');
                    notifDot.classList.add('bg-gray-300', 'dark:bg-zinc-700');
                }
                if (notifCard) {
                    notifCard.classList.remove('bg-blue-50', 'dark:bg-blue-950/20', 'border-l-2',
                        'border-blue-500');
                }

                const badge = document.querySelector('.bell-badge');
                if (badge) {
                    const newCount = parseInt(badge.textContent.trim()) - 1;
                    if (newCount <= 0) {
                        badge.remove();
                    } else {
                        badge.textContent = newCount;
                    }
                }
            });
    }
</script>
