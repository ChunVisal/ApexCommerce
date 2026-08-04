<nav
    class="bg-white dark:bg-black border-b border-gray-300 dark:border-zinc-800 px-4 py-2 flex items-center justify-between sticky top-0 z-40 transition-colors duration-200">

    <!-- Logo Section -->
    <a href="{{ route('admin.dashboard') }}" class="flex items-center">
        @php $logo = App\Models\Setting::get('logo'); @endphp

        @if ($logo)
            <img src="{{ $logo }}" class="h-[41px] w-auto max-w-[120px] object-contain shrink-0" />
        @else
            <img src="{{ asset('images/logo.png') }}"
                class="h-[41px] w-auto max-w-[120px] object-contain shrink-0 dark:hidden" />
            <img src="{{ asset('images/logodarkmode.png') }}"
                class="h-[41px] w-auto max-w-[120px] object-contain shrink-0 hidden dark:block" />
        @endif
    </a>

    <!-- Center Section: Search & Actions  -->
    <div class="flex-1 flex items-center justify-end gap-4 min-w-0">

        <!-- Dark Mode Toggle -->
        <x-dark-mode-toggle />

        <!-- Notifications Dropdown (now includes Order notifications) -->
        <div class="relative flex-shrink-0" x-data="{ open: false }">
            <button @click="open = !open"
                class="relative p-2 text-gray-700 dark:text-zinc-300 hover:text-p dark:hover:text-[#138cb3] hover:bg-gray-100 dark:hover:bg-zinc-900 rounded-full transition-colors focus:outline-none">
                <i class="fa-solid fa-bell text-xl"></i>
                @php
                    // StockRequests badge count (pending & loss_reported, unseen)
                    $pendingStockCount = \App\Models\StockRequest::whereIn('status', ['pending', 'loss_reported'])
                        ->whereNull('seen_at')
                        ->count();

                    // Orders pending approval count (e.g. status 'pending_approval', unseen)
                    $pendingOrderCount = \App\Models\Order::where('status', 'pending_approval')->count();

                    $pendingCount = $pendingStockCount + $pendingOrderCount;
                @endphp
                @if ($pendingCount > 0)
                    <span
                        class="absolute top-1 right-1 bell-badge bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $pendingCount }}
                    </span>
                @endif
            </button>

            <!-- Dropdown List -->
            <div x-show="open" @click.outside="open = false" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-xl dark:shadow-zinc-950/50 z-50 overflow-hidden">
                <!-- Dropdown Header -->
                <div
                    class="px-4 py-3 bg-gray-50/50 dark:bg-zinc-900/40 border-b border-gray-100 dark:border-zinc-800/60 flex items-center justify-between">
                    <h3 class="text-xs font-bold tracking-tight text-gray-900 dark:text-zinc-100 uppercase">
                        Notifications
                    </h3>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="markAllRead()"
                            class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1 text-p dark:text-[#1389af] hover:text-cyan-700 dark:hover:text-cyan-400 transition-colors"
                            title="Mark all notifications as read">
                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                            <span>Mark read</span>
                        </button>
                        <span class="w-[1px] h-3 bg-gray-200 dark:bg-zinc-800"></span>
                        <a href="{{ route('admin.notifications') }}"
                            class="text-[11px] font-bold text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100 hover:underline uppercase tracking-wider transition-colors">
                            View All
                        </a>
                    </div>
                </div>

                <!-- Notification List: Merge Stock and Order notifications -->
                <div
                    class="max-h-[320px] tab-container overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800/40">
                    @php
                        // StockRequest notifications
                        $stockRequests = \App\Models\StockRequest::with(['cashier', 'product'])
                            ->whereIn('status', ['pending', 'loss_reported', 'refunded'])
                            ->latest()
                            ->limit(6)
                            ->get()
                            ->map(function ($item) {
                                $item->notif_type = 'stock_request';
                                return $item;
                            });

                        // Order notifications (e.g. status 'pending_approval')
                        $orderNotifications = \App\Models\Order::with(['customer'])
                            ->where('status', 'pending_approval')
                            ->latest()
                            ->limit(6)
                            ->get()
                            ->map(function ($item) {
                                $item->notif_type = 'order';
                                return $item;
                            });

                        // Combine and sort by created_at descending, and limit to 6 total
                        $notifications = $stockRequests
                            ->concat($orderNotifications)
                            ->sortByDesc(function ($item) {
                                return $item->created_at;
                            })
                            ->take(6);
                    @endphp

                    @forelse($notifications as $notif)
                        @if ($notif->notif_type === 'stock_request')
                            <div class="notif-card flex items-start gap-3 px-4 py-3 transition-colors
                        {{ empty($notif->seen_at) ? 'bg-blue-50 dark:bg-blue-950/20 border-l-2 border-blue-500' : 'hover:bg-gray-50/60 dark:hover:bg-zinc-800/30' }}"
                                data-notif-id="s{{ $notif->id }}" style="cursor:pointer"
                                @click.stop="markSingleRead('stock_request:{{ $notif->id }}', $event.target)">

                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-11 h-11 rounded-md bg-gray-100 dark:bg-zinc-850 border border-gray-200/60 dark:border-zinc-800 overflow-hidden flex items-center justify-center">
                                        @if (!empty($notif->product->image))
                                            <img src="{{ $notif->product->image }}" alt="{{ $notif->product->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <img src="https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png"
                                                alt="Placeholder" class="w-full h-full object-cover">
                                        @endif
                                    </div>

                                    @if ($notif->status === 'loss_reported')
                                        <div
                                            class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900 bg-red-500">
                                            <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-white" />
                                        </div>
                                    @elseif ($notif->status === 'refunded')
                                        <div
                                            class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900 bg-blue-500">
                                            <x-heroicon-s-arrow-uturn-left class="w-3 h-3 text-white" />
                                        </div>
                                    @else
                                        <div
                                            class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900 bg-amber-500">
                                            <x-heroicon-s-clock class="w-2.5 h-2.5 text-white" />
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-1 min-w-0 justify-between items-center gap-2">
                                    <div class="flex-1 min-w-0 space-y-0.5">
                                        <p class="text-xs text-gray-800 dark:text-zinc-200 leading-snug break-words">
                                            <span
                                                class="font-bold text-gray-950 dark:text-zinc-50">{{ $notif->cashier->name }}</span>
                                            @if ($notif->status === 'loss_reported')
                                                <span class="text-red-600 dark:text-red-400">reported loss of</span>
                                            @elseif ($notif->status === 'refunded')
                                                <span class="text-p dark:text-blue-400">restocked (refund)</span>
                                            @else
                                                <span class="text-amber-600 dark:text-amber-400">requested</span>
                                            @endif
                                            <span
                                                class="font-bold text-p dark:text-[#1389af]">{{ $notif->quantity_requested }}x</span>
                                            @if ($notif->product && $notif->product->base_unit_name)
                                                <span class="text-p font-bold dark:text-[#1389af]">
                                                    {{ $notif->product->base_unit_name }}</span>
                                            @endif

                                            <span
                                                class="font-medium text-gray-900 dark:text-zinc-100">{{ $notif->product->name ?? ($notif->product_name ?? 'Unknown Product') }}</span>

                                        </p>
                                        <div class="flex items-center gap-1.5 pt-0.5 flex-wrap">
                                            @if ($notif->status === 'refunded')
                                                <span
                                                    class="text-[10px] font-bold tracking-normal bg-blue-50 dark:bg-blue-950/30 text-p dark:text-blue-400 border border-blue-100 dark:border-blue-950/40 px-1 py-0.5 rounded">
                                                    Refunded
                                                </span>
                                            @elseif ($notif->status !== 'loss_reported')
                                                <span
                                                    class="text-[10px] font-bold tracking-normal bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-950/40 px-1 py-0.5 rounded">
                                                    Pending
                                                </span>
                                            @endif
                                            <span class="text-[10px] text-gray-400 dark:text-zinc-500 font-medium">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <button
                                        @click.stop="markSingleRead('stock_request:{{ $notif->id }}', $event.target)"
                                        class="notif-dot ml-2 w-2.5 h-2.5 rounded-full shrink-0
                                    {{ $notif->seen_at ? 'bg-gray-300 dark:bg-zinc-700' : 'bg-red-500' }}"
                                        title="Mark as read" aria-label="Mark as read" type="button">
                                    </button>
                                </div>
                            </div>
                        @elseif ($notif->notif_type === 'order')
                            <div class="notif-card flex items-start gap-3 px-4 py-3 transition-colors
                                {{ empty($notif->admin_seen_at) ? 'bg-green-50 dark:bg-green-950/20 border-l-2 border-green-500' : 'hover:bg-gray-50/60 dark:hover:bg-zinc-800/30' }}"
                                data-notif-id="o{{ $notif->id }}" style="cursor:pointer"
                                @click.stop="markSingleRead('order:{{ $notif->id }}', $event.target)">
                                <!-- Order Icon -->
                                <div class="relative flex-shrink-0">
                                    <div
                                        class="w-11 h-11 rounded-md bg-green-100 dark:bg-green-950/40 border border-green-100 dark:border-green-950/30 flex items-center justify-center">
                                        <i class="fa fa-shopping-bag text-green-700 dark:text-green-300 text-xl"></i>
                                    </div>
                                    <div
                                        class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900 bg-green-500">
                                        <x-heroicon-s-arrow-up-tray class="w-3 h-3 text-white" />
                                    </div>
                                </div>
                                <!-- Notification Text & Actions -->
                                <div class="flex flex-1 min-w-0 justify-between items-center gap-2">
                                    <div class="flex-1 min-w-0 space-y-0.5">
                                        <p class="text-xs text-gray-800 dark:text-zinc-200 leading-snug break-words">
                                            <span class="font-bold text-green-900 dark:text-green-200">
                                                {{ $notif->customer->name ?? 'Customer' }}
                                            </span>
                                            <span class="text-green-600 dark:text-green-300">
                                                submitted order
                                            </span>
                                            <span class="font-medium text-gray-900 dark:text-zinc-100">
                                                #{{ $notif->id }}
                                            </span>
                                        </p>
                                        <div class="flex items-center gap-1.5 pt-0.5 flex-wrap">
                                            <span
                                                class="text-[10px] font-bold tracking-normal bg-green-50 dark:bg-green-950/30 text-green-600 dark:text-green-400 border border-green-100 dark:border-green-950/30 px-1 py-0.5 rounded">
                                                Pending Approval
                                            </span>
                                            <span class="text-[10px] text-gray-400 dark:text-zinc-500 font-medium">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <button @click.stop="markSingleRead('order:{{ $notif->id }}', $event.target)"
                                        class="notif-dot ml-2 w-2.5 h-2.5 rounded-full shrink-0
                                            {{ $notif->admin_seen_at ? 'bg-gray-300 dark:bg-zinc-700' : 'bg-red-500' }}"
                                        title="Mark as read" aria-label="Mark as read" type="button">
                                    </button>
                                </div>
                            </div>
                        @endif
                    @empty
                        <!-- Empty Placeholder Visual Representation Block -->
                        <div class="px-4 py-12 text-center text-xs font-medium text-gray-400 dark:text-zinc-500">
                            <x-heroicon-o-check-circle class="w-6 h-6 mx-auto mb-2 opacity-60" />
                            No pending requests
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- User Avatar & Info -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold overflow-hidden"
                style="background-color: {{ auth()->user()->role === 'admin' ? '#8B5CF6' : '#0F6E8C' }};">
                @if (auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                @else
                    <span class="text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
            <div class="hidden sm:block leading-none">
                <p class="font-semibold text-sm text-gray-800 dark:text-zinc-200">
                    {{ auth()->user()->name ?? 'Guest' }}
                </p>
                <span
                    class="text-xs text-p dark:text-[#138cb3] font-medium">{{ auth()->user()->role ?? 'User' }}</span>
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
        fetch('/admin/notifications/mark-all-read', {
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
                        'bg-green-50',
                        'dark:bg-green-950/20',
                        'border-l-2',
                        'border-blue-500',
                        'border-green-500'
                    );
                });

                // clear the bell badge count
                const badge = document.querySelector('.bell-badge');
                if (badge) badge.remove();
            });
    }

    function markSingleRead(idStr, el) {
        // idStr: "stock_request:123" or "order:456"
        let [type, id] = String(idStr).split(':');
        let url = '';
        if (type === "order") {
            url = `/admin/orders/${id}/mark-notif-read`;
        } else {
            // default to stock_request (legacy)
            url = `/admin/notifications/${id}/mark-read`;
        }

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                let notifCard, notifDot;
                // Find the notif card and dot by element or using [data-notif-id]
                if (el.classList.contains('notif-dot')) {
                    notifDot = el;
                    notifCard = el.closest('.notif-card');
                } else if (el.classList.contains('notif-card')) {
                    notifCard = el;
                    notifDot = notifCard.querySelector('.notif-dot');
                } else {
                    notifCard = document.querySelector(`.notif-card[data-notif-id="${type.charAt(0)}${id}"]`);
                    notifDot = notifCard ? notifCard.querySelector('.notif-dot') : null;
                }

                if (notifDot) {
                    notifDot.classList.remove('bg-blue-500', 'bg-red-500');
                    notifDot.classList.add('bg-gray-300', 'dark:bg-zinc-700');
                }
                if (notifCard) {
                    notifCard.classList.remove(
                        'bg-blue-50', 'dark:bg-blue-950/20', 'border-l-2', 'border-blue-500',
                        'bg-green-50', 'dark:bg-green-950/20', 'border-green-500'
                    );
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
