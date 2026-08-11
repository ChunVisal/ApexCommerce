<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public static function getSummaryCards($start = null, $end = null)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todaySales = Order::where('status', '!=', 'refunded')->whereDate('created_at', $today)->sum('total');
        $yesterdaySales = Order::where('status', '!=', 'refunded')->whereDate('created_at', $yesterday)->sum('total');
        $todayOrders = Order::where('status', '!=', 'refunded')->whereDate('created_at', $today)->count();

        // Percentage change from yesterday
        $salesChange = $yesterdaySales > 0
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
            : 0;

        $totalOrders = Order::where('status', '!=', 'refunded')->count();
        $totalRevenue = Order::where('status', '!=', 'refunded')->sum('total');
        $lowStock = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)->count();
        $outOfStock = Product::where('stock_quantity', '<=', 0)->count();

        return [
            [
                'title' => 'Total Revenue',
                'value' => '$' . number_format($totalRevenue, 2),
                'icon' => 'fa-solid fa-dollar-sign',
                'iconBg' => '#FFD700', // gold color
                'iconColor' => '#FFD700', // gold color
                'trend' => 'up',
                'percentage' => 'All time',
                'period' => 'Lifetime earnings',
                'highlight' => true,
            ],
            [
                'title' => 'Sales Today',
                'value' => '$' . number_format($todaySales, 2),
                'icon' => 'fa-solid fa-cart-shopping',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'trend' => $salesChange >= 0 ? 'up' : 'down',
                'percentage' => abs($salesChange) . '%',
                'period' => 'Yesterday: $' . number_format($yesterdaySales, 2),
            ],
            [
                'title' => 'Total Orders',
                'value' => $totalOrders,
                'icon' => 'fa-solid fa-receipt',
                'iconBg' => '#8B5CF6',
                'iconColor' => '#8B5CF6',
                'trend' => 'up',
                'percentage' => $todayOrders . ' today',
                'period' => 'All time',
            ],
            [
                'title' => 'Low Stock Alert',
                'value' => $lowStock,
                'icon' => 'fa-solid fa-triangle-exclamation',
                'iconBg' => '#EF4444',
                'iconColor' => '#EF4444',
                'trend' => 'down',
                'percentage' => $outOfStock . ' items',
                'period' => 'Out Of Stock',
            ],
        ];
    }

    public static function getSalesChart($start = null, $end = null)
    {
        $start = $start ? Carbon::parse($start) : now()->subDays(13);
        $end = $end ? Carbon::parse($end) : now();
        $totalRevenue = Order::where('status', '!=', 'refunded')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $data = [];
        $current = $start->copy();
        while ($current <= $end) {
            $data[] = [
                // chart's X-axis
                'label_short' => $current->format('M d'),
                'label_full' => $current->format('D, M d, Y'),
                'total' => Order::where('status', '!=', 'refunded')
                    ->whereDate('created_at', $current)
                    ->sum('total') ?: 0,
            ];
            $current->addDay();
        }
        return [
            'chart' => $data,
            'total_revenue' => $totalRevenue,
        ];
    }

    public static function getPaymentBreakdown()
    {
        $cash = Payment::where('method', 'cash')->count();
        $card = Payment::where('method', 'card')->count();
        $khqr = Payment::where('method', 'khqr')->count();
        $total = $cash + $card + $khqr ?: 1;

        return [
            'cash' => round(($cash / $total) * 100),
            'card' => round(($card / $total) * 100),
            'khqr' => round(($khqr / $total) * 100),
        ];
    }

    public static function getTopProducts($limit = 5)
    {
        $items = OrderItem::whereHas('order', fn($q) => $q->where('status', '!=', 'refunded'))
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(
                'order_items.name',
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                // Prorate: each item's share of the order's actual discount, based on its portion of the subtotal
                DB::raw('SUM(order_items.total - (order_items.total / NULLIF(orders.subtotal, 0)) * orders.discount) as net_revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
            )
            ->groupBy('order_items.name', 'order_items.product_id')
            ->orderByDesc('net_revenue')
            ->limit($limit)
            ->get();

        $maxRevenue = $items->max('net_revenue') ?: 1;

        return $items->map(function ($item, $index) use ($maxRevenue) {
            $product = Product::with('category')->find($item->product_id);

            return [
                'rank' => $index + 1,
                'name' => $item->name,
                'category' => $product->category->name ?? '-',
                'image' => $product->image ?? null,
                'price' => $product->selling_price ?? 0,
                'sold' => $item->total_qty,
                'revenue' => round($item->net_revenue, 2),
                'avg_sale_price' => $item->total_qty > 0
                    ? round($item->net_revenue / $item->total_qty, 2)
                    : 0,
                'percent' => round(($item->net_revenue / $maxRevenue) * 100),
            ];
        })->toArray();
    }

    public static function getTopCategories($limit = 5)
    {
        $items = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'refunded'))
            ->select(
                'categories.id',
                'categories.name',
                'categories.svg',
                'categories.code',
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
                DB::raw('COUNT(DISTINCT products.id) as product_count'),
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('ROUND(SUM(order_items.total) / SUM(order_items.quantity), 2) as avg_sale_price'),
                DB::raw('ROUND(SUM(order_items.total) / COUNT(DISTINCT order_items.order_id), 2) as avg_order_value')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.code', 'categories.svg')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        $maxRevenue = $items->max('total_revenue') ?: 1;

        return $items->map(function ($item, $index) use ($maxRevenue) {
            return [
                'rank' => $index + 1,
                'code' => $item->code,
                'name' => $item->name,
                'svg' => $item->svg,
                'products' => $item->product_count,
                'orders' => $item->order_count,
                'sold' => $item->total_qty,
                'revenue' => $item->total_revenue,
                'avg_sale_price' => $item->avg_sale_price,
                'avg_order_value' => $item->avg_order_value,
                'percent' => round(($item->total_revenue / $maxRevenue) * 100),

            ];
        })->toArray();
    }
}
