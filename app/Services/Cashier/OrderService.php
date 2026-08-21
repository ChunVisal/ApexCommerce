<?php

namespace App\Services\Cashier;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public static function getSummaryCards()
    {
        $userId = Auth::id();

        $totalSales = Order::where('cashier_id', $userId)
            ->where('status', '!=', 'refunded')
            ->sum('total');
        $todaySales = Order::where('cashier_id', $userId)
            ->where('status', '!=', 'refunded')
            ->whereDate('created_at', now()->toDateString())
            ->sum('total');

        $totalOrders = Order::where('cashier_id', $userId)
            ->where('status', '!=', 'refunded')
            ->count();

        $totalItems = Order::where('cashier_id', $userId)
            ->where('status', '!=', 'refunded')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');

        $avgOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return [
            [
                'title' => 'Total Sales',
                'value' => '$' . number_format($totalSales, 2),
                'icon' => 'fa-solid fa-dollar-sign',
                'iconBg' => '#10B981',
                'iconColor' => '#10B981',
                'dot' => '#10B981',
                'subtitle' => '$' . number_format($todaySales, 2) . ' Today',
            ],
            [
                'title' => 'Average Order',
                'value' => '$' . number_format($avgOrder, 2),
                'icon' => 'fa-solid fa-chart-line',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'dot' => '#10B981',
                'subtitle' => 'All time',
            ],
            [
                'title' => 'Items Sold',
                'value' => $totalItems,
                'icon' => 'fa-solid fa-cubes',
                'iconBg' => '#F59E0B',
                'iconColor' => '#F59E0B',
                'dot' => '#F59E0B',
                'subtitle' => 'Products moved',
            ],
            [
                'title' => 'Total Orders',
                'value' => $totalOrders,
                'icon' => 'fa-solid fa-receipt',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'dot' => '#0F6E8C',
                'subtitle' => 'Completed sales',
            ],
        ];
    }
    
    public static function getCashierOrders($cashierId, $payment = null, $search = null, $filter = null)
    {
        return Order::with(['items', 'payment', 'customer'])
            ->where('cashier_id', $cashierId)
            ->when($payment && $payment !== 'all', function ($q) use ($payment) {
                $q->whereHas('payment', fn($p) => $p->where('method', $payment));
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('items', fn($i) => $i->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filter && $filter !== 'all_time', function ($q) use ($filter) {
                match ($filter) {
                    'today' => $q->whereDate('created_at', today()),
                    'yesterday' => $q->whereDate('created_at', now()->subDay()->toDateString()),
                    'last_7_days' => $q->where('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $q->where('created_at', '>=', now()->subDays(30)),
                    default => null,
                };
            })
            ->latest()
            ->get();
    }
}
