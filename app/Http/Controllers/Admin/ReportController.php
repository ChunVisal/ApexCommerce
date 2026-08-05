<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Admin\ReportService;
use App\Services\Admin\ActivityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(14);
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();

        $summaryCards = ReportService::getSummaryCards($request->start_date, $request->end_date);

        // Daily Sales
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

        // ✅ ADD THIS - Fill missing dates with zero
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

        $dailySales = collect(array_values($allDates));

        $topCashiers = Order::where('orders.status', '!=', 'refunded')
            ->whereBetween('orders.created_at', [$start, $end])
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->where('users.role', 'cashier')  // ← Only cashiers, not admin
            ->select(
                'users.id',
                'users.name',
                'users.avatar',
                'users.employee_id',
                'users.shift',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(orders.total) as revenue'),
                DB::raw('SUM(orders.discount) + SUM(orders.vip_discount) as discount')
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

        // All Orders
        $orders = Order::with(['cashier', 'customer', 'payment', 'items'])
            ->whereBetween('orders.created_at', [$start, $end])
            ->latest()
            ->get();

        return view('admin.reports.index', compact(
            'summaryCards',
            'dailySales',
            'topCashiers',
            'orders',
            'start',
            'end'
        ));
    }

    public function export(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(14);
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();

        $orders = Order::with(['cashier', 'customer', 'payment'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $filename = 'orders_report_' . now()->format('Y_m_d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($orders, $start, $end) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ORDERS REPORT', $start->format('M d, Y') . ' - ' . $end->format('M d, Y')]);
            fputcsv($file, ['Order Number', 'Cashier', 'Customer', 'Items', 'Total', 'Payment', 'Status', 'Date']);
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->cashier->name ?? '-',
                    $order->customer->name ?? 'Walk-in',
                    $order->items->sum('quantity'),
                    number_format($order->total, 2),
                    $order->payment->method ?? '-',
                    $order->status,
                    $order->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        ActivityService::log('Report_exported', 'exported Report report (CSV)', 'Report', 'info');

        return response()->stream($callback, 200, $headers);
    }
}
