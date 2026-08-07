@props(['role' => 'cashier'])

@php
    $isAdmin = $role === 'admin';

    if ($isAdmin) {
        $stockRequests = \App\Models\StockRequest::with(['cashier', 'product'])
            ->whereIn('status', ['pending', 'loss_reported', 'refunded'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($item) {
                $item->notif_type = 'stock_request';
                return $item;
            });

        $notifications = $stockRequests;

        $pendingStockCount = \App\Models\StockRequest::whereIn('status', ['pending', 'loss_reported'])
            ->whereNull('seen_at')
            ->count();

        $count = $pendingStockCount;

        $urlPrefix = '/admin/notifications';
        $viewAllUrl = route('admin.notifications');
    } else {
        $notifications = \App\Models\StockRequest::with(['product', 'approver'])
            ->where('cashier_id', auth()->id())
            ->where('created_at', '>=', now()->subDays(30))
            ->whereIn('status', ['pending', 'approved', 'rejected', 'on_hold'])
            ->latest()
            ->limit(6)
            ->get()
            ->sortBy(function ($notif) {
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
            })
            ->map(function ($item) {
                $item->notif_type = 'stock_request';
                return $item;
            });

        $count = \App\Models\StockRequest::where('cashier_id', auth()->id())
            ->where('created_at', '>=', now()->subDays(15))
            ->whereIn('status', ['pending', 'approved', 'rejected', 'on_hold'])
            ->whereNull('seen_at')
            ->count();

        $urlPrefix = '/cashier/notifications';
        $viewAllUrl = route('cashier.notifications');
    }
@endphp

<div class="relative" x-data="notificationBell('{{ $urlPrefix }}')">
    <button @click="open = !open"
        class="relative p-2 text-gray-700 dark:text-zinc-300 hover:text-[#0F6E8C] dark:hover:text-[#138cb3] hover:bg-gray-100 dark:hover:bg-zinc-900 rounded-full transition-colors">
        <i class="fa-solid fa-bell text-xl"></i>
        @if ($count > 0)
            <span
                class="bell-badge absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                {{ $count }}
            </span>
        @endif
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md shadow-xl dark:shadow-zinc-950/50 z-50 overflow-hidden">

        <div
            class="px-4 py-3 bg-gray-50/50 dark:bg-zinc-900/40 border-b border-gray-100 dark:border-zinc-800/60 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-tight text-gray-900 dark:text-zinc-100">Notifications</h3>
            <div class="flex items-center gap-3">
                <button type="button" @click="markAllRead()"
                    class="text-[11px] font-bold uppercase tracking-wider flex items-center gap-1 text-[#0F6E8C] dark:text-[#1389af] hover:text-cyan-700 dark:hover:text-cyan-400 transition-colors">
                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                    <span>Mark read</span>
                </button>
                <span class="w-[1px] h-3 bg-gray-200 dark:bg-zinc-800"></span>
                <a href="{{ $viewAllUrl }}"
                    class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100 hover:underline transition-colors">
                    View All
                </a>
            </div>
        </div>

        <div class="max-h-[320px] tab-container overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800/40">
            @forelse($notifications as $notif)
                @if ($notif->notif_type === 'stock_request')
                    @php
                        $unread = $isAdmin ? empty($notif->seen_at) : empty($notif->seen_at);
                        $refKey = $notif->id;
                    @endphp
                    <div class="notif-card cursor-pointer flex items-start gap-3 px-4 py-3 transition-colors
                        {{ $unread ? 'bg-blue-50 dark:bg-blue-950/20 border-l-2 border-blue-500' : 'hover:bg-gray-50/60 dark:hover:bg-zinc-800/30' }}"
                        @click="markSingleRead({{ $notif->id }}, $event.currentTarget)">

                        <div class="relative flex-shrink-0">
                            <div
                                class="w-11 h-11 rounded-md bg-gray-100 dark:bg-zinc-850 border border-gray-200/60 dark:border-zinc-800 overflow-hidden flex items-center justify-center">
                                @if (!empty($notif->product->image))
                                    <img src="{{ $notif->product->image }}" class="w-full h-full object-cover">
                                @else
                                    <img src="https://res.cloudinary.com/dexr27qho/image/upload/v1782723706/8fc9e618-ca35-4366-a173-ae4d15ec0aef_vyjksv.png"
                                        class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div
                                class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900
                                {{ $notif->status === 'approved' ? 'bg-green-500' : ($notif->status === 'rejected' || $notif->status === 'loss_reported' ? 'bg-red-500' : ($notif->status === 'refunded' ? 'bg-blue-500' : 'bg-amber-500')) }}">
                                @if ($notif->status === 'approved')
                                    <x-heroicon-s-check class="w-2.5 h-2.5 text-white" />
                                @elseif (in_array($notif->status, ['rejected', 'loss_reported']))
                                    <x-heroicon-s-x-mark class="w-2.5 h-2.5 text-white" />
                                @elseif ($notif->status === 'refunded')
                                    <x-heroicon-s-arrow-uturn-left class="w-3 h-3 text-white" />
                                @else
                                    <x-heroicon-s-clock class="w-2.5 h-2.5 text-white" />
                                @endif
                            </div>
                        </div>

                        <div class="flex-1 min-w-0 space-y-0.5">
                            <p class="text-xs text-gray-800 dark:text-zinc-200 leading-snug break-words">
                                @if ($isAdmin)
                                    <span class="font-bold">{{ $notif->cashier->name }}</span>
                                    @if ($notif->status === 'loss_reported')
                                        <span class="text-red-600 dark:text-red-400">reported loss of</span>
                                    @elseif ($notif->status === 'refunded')
                                        <span class="text-[#0F6E8C] dark:text-blue-400">restocked (refund)</span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">requested</span>
                                    @endif
                                    <span
                                        class="font-bold text-[#0F6E8C] dark:text-[#1389af]">{{ $notif->quantity_requested }}x</span>
                                @else
                                    @if ($notif->quantity_approved)
                                        <span class="font-extrabold">({{ $notif->quantity_approved }} sent)</span>
                                        <span class="font-medium">{{ $notif->quantity_requested }}x</span>
                                    @else
                                        <span class="font-bold">{{ $notif->quantity_requested }}x</span>
                                    @endif
                                @endif
                                <span
                                    class="font-medium">{{ $notif->product->name ?? ($notif->product_name ?? 'Unknown') }}
                                    @if ($notif->product && $notif->product->base_unit_name)
                                        <span class="text-gray-700 dark:text-zinc-400">
                                            ({{ $notif->product->base_unit_name }})
                                        </span>
                                    @endif
                                </span>
                            <div class="flex gap-1">
                                <p
                                    class="text-xs font-normal tracking-normal
                                    {{ $notif->status === 'approved' ? 'text-green-600' : ($notif->status === 'rejected' ? 'text-red-600' : 'text-amber-600') }}">
                                    {{ $notif->status === 'approved' ? 'Approved' : ($notif->status === 'rejected' ? 'Rejected' : ($notif->status === 'on_hold' ? 'On Hold' : 'Pending')) }}
                                </p>
                                </p>
                                <p class="text-[11px] text-gray-400 dark:text-zinc-500 font-medium">
                                    {{ $notif->updated_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        <button
                            class="notif-dot ml-2 w-2.5 h-2.5 rounded-full shrink-0
                                {{ $notif->seen_at ? 'bg-gray-300 dark:bg-zinc-700' : 'bg-red-500' }}"
                            title="Mark as read" type="button"></button>
                    </div>
                @endif
            @empty
                <div class="px-4 py-12 text-center text-xs font-medium text-gray-400 dark:text-zinc-500">
                    <x-heroicon-o-bell-slash class="w-6 h-6 mx-auto mb-2 opacity-60" />
                    No notifications
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function notificationBell(urlPrefix) {
        return {
            open: false,

            async markAllRead() {
                await fetch(urlPrefix + '/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                document.querySelector('.bell-badge')?.remove();

                document.querySelectorAll('.notif-dot').forEach(dot => {
                    dot.classList.remove('bg-red-500');
                    dot.classList.add('bg-gray-300', 'dark:bg-zinc-700');
                });

                document.querySelectorAll('.notif-card').forEach(card => {
                    card.classList.remove('bg-blue-50', 'dark:bg-blue-950/20', 'border-l-2',
                        'border-blue-500');
                });
            },

            markSingleRead(id, card) {
                fetch(`${urlPrefix}/${id}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(res => res.json()).then(data => {
                    if (!data.success) return;

                    const dot = card.querySelector('.notif-dot');
                    if (dot) {
                        dot.classList.remove('bg-red-500');
                        dot.classList.add('bg-gray-300', 'dark:bg-zinc-700');
                    }

                    card.classList.remove('bg-blue-50', 'dark:bg-blue-950/20', 'border-l-2', 'border-blue-500');

                    const badge = document.querySelector('.bell-badge');
                    if (badge) {
                        const count = parseInt(badge.textContent) - 1;
                        count <= 0 ? badge.remove() : (badge.textContent = count);
                    }

                });
            }
        };
    }
</script>
