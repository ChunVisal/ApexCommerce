<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Admin\ReportService;
use App\Services\Admin\ActivityService;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(14);
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();
        $summaryCards = ReportService::getSummaryCards($request->start_date, $request->end_date);
        $dailySales = ReportService::getDailySales($start, $end);
        $topCashiers = ReportService::getTopCashiers($start, $end);
        $paymentBreakdown = ReportService::getPaymentBreakdown($start, $end);
        $orders = ReportService::getOrders($start, $end);

        return view('admin.reports.index', compact(
            'summaryCards',
            'dailySales',
            'orders',
            'topCashiers',
            'paymentBreakdown',
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
