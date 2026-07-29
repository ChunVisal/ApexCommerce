<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityHelper;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public static function getSummaryCards($start = null, $end = null)
    {
        if ($start && $end) {
            $periodStart = Carbon::parse($start)->startOfDay();
            $periodEnd = Carbon::parse($end)->endOfDay();

            $todaySales = Order::where('status', '!=', 'refunded')->whereDate('created_at', today())->sum('total');
            $yesterdaySales = Order::where('status', '!=', 'refunded')->whereDate('created_at', yesterday())->sum('total');
            $todayOrders = Order::whereBetween('created_at', [$periodStart, $periodEnd])->count();
        } else {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            $todaySales = Order::where('status', '!=', 'refunded')->whereDate('created_at', $today)->sum('total');
            $yesterdaySales = Order::where('status', '!=', 'refunded')->whereDate('created_at', $yesterday)->sum('total');
            $todayOrders = Order::where('status', '!=', 'refunded')->whereDate('created_at', $today)->count();
        }
        // Percentage change from yesterday
        $salesChange = $yesterdaySales > 0
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
            : 0;

        $totalOrders = Order::where('status', '!=', 'refunded')->count();
        $totalRevenue = Order::where('status', '!=', 'refunded')->sum('total');
        $lowStock = Product::whereColumn('stock_quantity', '<', 'low_stock_threshold')
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
                'percentage' => Order::whereDate('created_at', today())->count() . ' today',
                'period' => 'All time',
            ],
            [
                'title' => 'Low Stock Alert',
                'value' => $lowStock + $outOfStock,
                'icon' => 'fa-solid fa-triangle-exclamation',
                'iconBg' => '#EF4444',
                'iconColor' => '#EF4444',
                'trend' => 'down',
                'percentage' => $outOfStock . ' items',
                'period' => 'Out Of Stock',
            ],
        ];
    }

    public static function getTopProducts($limit = 5)
    {
        $items = OrderItem::whereHas('order', fn($q) => $q->where('status', '!=', 'refunded'))
            ->select(
                'name',
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count'),
                DB::raw('ROUND(SUM(total) / SUM(quantity), 2) as avg_sale_price')
            )
            ->groupBy('name', 'product_id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        $maxRevenue = $items->max('total_revenue') ?: 1;

        return $items->map(function ($item, $index) use ($maxRevenue) {
            $product = Product::with('category')->find($item->product_id);

            return [
                'rank' => $index + 1,
                'name' => $item->name,
                'category' => $product->category->name ?? '-',
                'image' => $product->image ?? null,
                'price' => $product->selling_price ?? 0,
                'sold' => $item->total_qty,
                'revenue' => $item->total_revenue,
                'avg_sale_price' => $item->avg_sale_price,
                'percent' => round(($item->total_revenue / $maxRevenue) * 100),
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

    public static function getTopCashiers($limit = 5)
    {
        return Order::where('orders.status', '!=', 'refunded')
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.employee_id',
                'users.avatar',
                'users.shift',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total) as total_revenue'),
                DB::raw('ROUND(AVG(orders.total), 2) as avg_order_value'),
                DB::raw('MAX(orders.created_at) as last_sale_at')
            )
            ->groupBy('users.id', 'users.name', 'users.employee_id', 'users.avatar', 'users.shift')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(function ($cashier) {
                $cashier->total_items_sold = OrderItem::whereHas('order', function ($q) use ($cashier) {
                    $q->where('cashier_id', $cashier->id)
                        ->where('orders.status', '!=', 'refunded');
                })->sum('quantity');

                return $cashier;
            });
    }

    public static function getSalesChart($start = null, $end = null)
    {
        $start = $start ? Carbon::parse($start) : now()->subDays(13);
        $end = $end ? Carbon::parse($end) : now();

        $data = [];
        $current = $start->copy();
        while ($current <= $end) {
            $data[] = [
                'label_short' => $current->format('M d'),
                'label_full' => $current->format('D, M d, Y'),
                'total' => Order::where('status', '!=', 'refunded')
                    ->whereDate('created_at', $current)
                    ->sum('total') ?: 0,
            ];
            $current->addDay();
        }

        return $data;
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

    public static function exportDashboard(Request $request)
    {
        $start = $request->start_date ?? now()->subDays(14)->format('Y-m-d');
        $end = $request->end_date ?? now()->format('Y-m-d');

        $filename = 'dashboard_report_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($start, $end) {
            $file = fopen('php://output', 'w');

            // Summary
            $summary = DashboardController::getSummaryCards($start, $end);
            fputcsv($file, ['DASHBOARD REPORT']);
            fputcsv($file, ['Period', Carbon::parse($start)->format('M d, Y') . ' - ' . Carbon::parse($end)->format('M d, Y')]);
            fputcsv($file, []);
            fputcsv($file, ['SUMMARY']);
            foreach ($summary as $card) {
                fputcsv($file, [$card['title'], $card['value']]);
            }
            fputcsv($file, []);

            // Top Products
            fputcsv($file, ['TOP PRODUCTS']);
            fputcsv($file, ['Rank', 'Product', 'Category', 'Price', 'Sold', 'Revenue', 'Performance %']);
            foreach (DashboardController::getTopProducts(20) as $p) {
                fputcsv($file, [$p['rank'], $p['name'], $p['category'], $p['price'], $p['sold'], $p['revenue'], $p['percent'] . '%']);
            }
            fputcsv($file, []);

            // Top Categories
            fputcsv($file, ['TOP CATEGORIES']);
            fputcsv($file, ['Rank', 'Category', 'Products', 'Sold', 'Revenue', 'Avg Order', 'Performance %']);
            foreach (DashboardController::getTopCategories(20) as $c) {
                fputcsv($file, [$c['rank'], $c['name'], $c['products'], $c['sold'], $c['revenue'], $c['avg_order_value'], $c['percent'] . '%']);
            }
            fputcsv($file, []);

            // Top Cashiers
            fputcsv($file, ['TOP CASHIERS']);
            fputcsv($file, ['Rank', 'Name', 'Orders', 'Revenue']);
            foreach (self::getTopCashiers(20) as $index => $cashier) {
                fputcsv($file, [
                    $index + 1,
                    $cashier->name,
                    $cashier->total_orders,
                    number_format($cashier->total_revenue, 2),
                ]);
            }
            fputcsv($file, []);

            // Sales Data
            fputcsv($file, ['DAILY SALES']);
            fputcsv($file, ['Date', 'Revenue']);
            foreach (DashboardController::getSalesChart($start, $end) as $day) {
                fputcsv($file, [$day['label_full'], $day['total']]);
            }

            fclose($file);
        };

        ActivityHelper::log(
            'report_exported',
            'Exported dashboard report',
            'Dashboard',
            'info'
        );

        return response()->stream($callback, 200, $headers);
    }
}
