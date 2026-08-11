<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public static function getSummaryCards($start = null, $end = null)
    {

        $start = $start ? Carbon::parse($start) : now()->subDays(14);
        $end = $end ? Carbon::parse($end) : now();

        // Previous period for comparison
        $days = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($days);
        $prevEnd = $start->copy()->subDay();

        $totalCustomers = Customer::whereHas('orders', fn($q) => $q->where('status', '!=', 'refunded'))->count();

        $totalItemsSold = OrderItem::whereHas('order', fn($q) => $q->where('status', '!=', 'refunded'))->sum('quantity');

        $prevItemsSold = OrderItem::whereHas('order', fn($q) => $q->where('status', '!=', 'refunded')
            ->whereBetween('created_at', [$prevStart, $prevEnd]))
            ->sum('quantity');

        $totalRevenue = Order::where('status', '!=', 'refunded')->sum('total');

        $prevRevenue = Order::where('status', '!=', 'refunded')
            ->whereBetween('created_at', [$prevStart, $prevEnd])->sum('total');

        $orderCount = Order::where('status', '!=', 'refunded')->count();

        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        // Calculate changes
        $revenueChange = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;
        $itemsChange = $prevItemsSold > 0 ? round((($totalItemsSold - $prevItemsSold) / $prevItemsSold) * 100, 1) : 0;

        return [
            [
                'title'      => 'Total Customers',
                'value'      => number_format($totalCustomers),
                'icon'       => 'fa-solid fa-users',
                'iconBg'     => '#0F6E8C',
                'iconColor'  => '#0F6E8C',
                'trend'      => 'up',
                'percentage' => 'All time',
                'period'     => 'Active buyers',
            ],
            [
                'title'      => 'Items Sold',
                'value'      => number_format($totalItemsSold),
                'icon'       => 'fa-solid fa-cubes',
                'iconBg'     => '#8B5CF6',
                'iconColor'  => '#8B5CF6',
                'trend'      => $itemsChange >= 0 ? 'up' : 'down',
                'percentage' => abs($itemsChange) . '%',
                'period'     => 'vs prev period',
            ],
            [
                'title'      => 'Total Revenue',
                'value'      => '$' . number_format($totalRevenue, 2),
                'icon'       => 'fa-solid fa-dollar-sign',
                'iconBg'     => '#10B981',
                'iconColor'  => '#10B981',
                'trend'      => $revenueChange >= 0 ? 'up' : 'down',
                'percentage' => abs($revenueChange) . '%',
                'period'     => 'vs prev period',
            ],
            [
                'title'      => 'Avg Order Value',
                'value'      => '$' . number_format($avgOrderValue, 2),
                'icon'       => 'fa-solid fa-chart-line',
                'iconBg'     => '#F59E0B',
                'iconColor'  => '#F59E0B',
                'trend'      => 'up',
                'percentage' => $orderCount . ' orders',
                'period'     => 'Per transaction',
            ],
        ];
    }

    public static function getDailySales($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $dailySales = Order::where('orders.status', '!=', 'refunded')
            ->whereDate('orders.created_at', '>=', $start)
            ->whereDate('orders.created_at', '<=', $end)
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(orders.total) as revenue'),
                DB::raw('SUM(orders.discount) as discount'),
                DB::raw('SUM(orders.vip_discount) as vip_discount'),
                DB::raw('SUM(orders.tax) as tax')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates with zero
        $allDates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $allDates[$dateKey] = (object) [
                'date' => $dateKey,
                'orders' => 0,
                'revenue' => 0,
                'discount' => 0,
                'vip_discount' => 0,
                'tax' => 0,
            ];
            $current->addDay();
        }

        foreach ($dailySales as $sale) {
            $allDates[$sale->date] = $sale;
        }

        return collect(array_values($allDates));
    }

    public static function getTopCashiers($start, $end)
    {
        return Order::where('orders.status', '!=', 'refunded')
            ->whereBetween('orders.created_at', [$start, $end])
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->where('users.role', 'cashier')
            ->select(
                'users.id',
                'users.name',
                'users.avatar',
                'users.employee_id',
                'users.shift',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(orders.total) as revenue'),
                DB::raw('SUM(orders.discount) + SUM(orders.vip_discount) + SUM(orders.tax) as discount')
            )
            ->groupBy(
                'users.id',
                'users.name',
                'users.avatar',
                'users.employee_id',
                'users.shift'
            )
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($cashier) {
                $cashier->items_sold = OrderItem::whereHas('order', fn($q) => $q->where('cashier_id', $cashier->id)
                    ->where('status', '!=', 'refunded'))
                    ->sum('quantity');
                $cashier->avg_order = $cashier->orders > 0 ? $cashier->revenue / $cashier->orders : 0;
                return $cashier;
            });
    }

    public static function getOrders($start, $end)
    {
        return Order::with(['cashier', 'customer', 'payment', 'items'])
            ->whereBetween('orders.created_at', [$start, $end])
            ->latest()
            ->get();
    }
}
