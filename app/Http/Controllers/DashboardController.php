<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Admin\DashboardService;
use App\Services\Admin\ActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date ?? now()->subDays(14)->format('Y-m-d');
        $end = $request->end_date ?? now()->format('Y-m-d');

        $totalRevenue = Order::where('status', '!=', 'refunded')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        return view('admin.dashboard', [
            'start' => $start,
            'end' => $end,
            'totalRevenue' => $totalRevenue,
            'summaryCards' => DashboardService::getSummaryCards(),
            'topProducts' => DashboardService::getTopProducts(),
            'topCategories' => DashboardService::getTopCategories(),
            'salesChart' => DashboardService::getSalesChart($start, $end),
            'paymentBreakdown' => DashboardService::getPaymentBreakdown(),

        ]);
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
            $summary = DashboardController::getSummaryCards();
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

        ActivityService::log(
            'report_exported',
            'Exported dashboard report',
            'Dashboard',
            'info'
        );

        return response()->stream($callback, 200, $headers);
    }
}
