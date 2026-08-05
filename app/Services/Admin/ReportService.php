<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Carbon\Carbon;

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
}
