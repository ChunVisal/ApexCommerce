<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialMovement;
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
        $paymentSummary = ReportService::getPaymentSummaryCards($start, $end);
        $orders = ReportService::getOrders($start, $end);
        $financials = ReportService::getFinancials($start, $end);

        return view('admin.reports.index', compact(
            'summaryCards',
            'dailySales',
            'orders',
            'financials',
            'topCashiers',
            'paymentBreakdown',
            'paymentSummary',
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

        $financials = FinancialMovement::with('user')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $dailySales = ReportService::getDailySales($start, $end);
        $paymentBreakdown = ReportService::getPaymentBreakdown($start, $end);
        $topCashiers = ReportService::getTopCashiers($start, $end);

        $filename = 'report_satistics' . now()->format('Y_m_d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($orders, $financials, $start, $end, $dailySales, $paymentBreakdown, $topCashiers) {
            $file = fopen('php://output', 'w');


            fputcsv($file, ['DATES', $start->format('M d, Y') . ' - ' . $end->format('M d, Y')]);
            fputcsv($file, []); // blank line as a section spacer

            // Section 1: Orders (existing)
            fputcsv($file, ['ORDERS REPORT']);
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

            fputcsv($file, []); // blank line as a section spacer

            fputcsv($file, ['FINANCIAL MOVEMENT REPORT']);
            fputcsv($file, ['Date', 'Type', 'Category', 'Net Amount', 'Amount', 'Reference', 'User', 'Source', 'Notes' ]);
            foreach ($financials as $financial) {
                fputcsv($file, [
                    $financial->created_at->format('Y-m-d H:i'),
                    $financial->type === 'in' ? 'Money In' : 'Money Out',
                    $financial->category,
                    $financial->net_amount,
                    $financial->amount,
                    $financial->reference,
                    $financial->user->name ?? '-',
                    $financial->source_type ? class_basename($financial->source_type) . ' #' . $financial->source_id : '-',
                    $financial->notes,
                ]);
            }

            fputcsv($file, []); // blank line as a section spacer

            // Section 2: Daily Sales
            fputcsv($file, ['DAILY SALES']);
            fputcsv($file, ['Date', 'Orders', 'Revenue', 'Discount', 'VIP Discount', 'Tax', 'Items Sold']);
            foreach ($dailySales as $day) {
                fputcsv($file, [
                    $day->date,
                    $day->orders,
                    number_format($day->revenue, 2),
                    number_format($day->discount, 2),
                    number_format($day->vip_discount, 2),
                    number_format($day->tax, 2),
                    $day->items_qty,
                ]);
            }

            fputcsv($file, []);

            // Section 3: Payment Breakdown
            fputcsv($file, ['PAYMENT BREAKDOWN']);
            fputcsv($file, ['Method', 'Count']);
            fputcsv($file, ['Cash', $paymentBreakdown['cash']]);
            fputcsv($file, ['Card', $paymentBreakdown['card']]);
            fputcsv($file, ['KHQR', $paymentBreakdown['khqr']]);

            fputcsv($file, []);

            // Section 4: Top Cashiers
            fputcsv($file, ['TOP CASHIERS']);
            fputcsv($file, ['Cashier', 'Orders', 'Revenue', 'Items Sold', 'Avg Order']);
            foreach ($topCashiers as $cashier) {
                fputcsv($file, [
                    $cashier->name,
                    $cashier->orders,
                    number_format($cashier->revenue, 2),
                    $cashier->items_sold,
                    number_format($cashier->avg_order, 2),
                ]);
            }

            fclose($file);
        };

        ActivityService::log('Report_exported', 'exported Report report (CSV)', 'Report', 'info');

        return response()->stream($callback, 200, $headers);
    }
}
