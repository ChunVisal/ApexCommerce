<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Admin\CustomerService;
use App\Services\Admin\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = CustomerService::getCustomersWithSegments();
        $summaryCards = CustomerService::getSummaryCards();

        return view('admin.customers.index', compact('customers', 'summaryCards'));
    }

    public function show(int $id)
    {
        $customer = Customer::findOrFail($id);

        // Fetch overall metrics for customer across all orders
        $totalOrders = $customer->orders()
            ->where('status', '!=', 'refunded')
            ->count();

        $totalSpent = $customer->orders()
            ->where('status', '!=', 'refunded')
            ->sum('total');

        $avgOrder = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        // Fetch recent order history for UI presentation
        $orders = Order::with('payment', 'items')
            ->where('customer_id', $id)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'segment' => $customer->segment,
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'avg_order' => round($avgOrder, 2),
                'last_order_at' => $customer->last_order_at,
                'created_at' => $customer->created_at,
            ],
            'orders' => $orders,
        ]);
    }

    public function getOrder(int $customerId, int $orderId)
    {
        $order = Order::with(['items', 'payment', 'customer'])
            ->where('customer_id', $customerId)
            ->findOrFail($orderId);

        return response()->json(['order' => $order]);
    }

    public function export()
    {
        $customers = CustomerService::getCustomersWithSegments();

        $filename = 'all_customers_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ALL CUSTOMERS REPORT']);
            fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['Name', 'Phone', 'Email', 'Segment', 'Total Orders', 'Total Spent', 'Last Order', 'Joined']);

            foreach ($customers as $c) {
                $totalOrders = $c->total_orders ?? 0;
                $totalSpent = $c->total_spent ?? 0;

                fputcsv($file, [
                    $c->name,
                    $c->phone,
                    $c->email ?? '-',
                    ucfirst($c->segment),
                    $totalOrders,
                    number_format($totalSpent, 2),
                    $c->last_order_at ? Carbon::parse($c->last_order_at)->format('Y-m-d') : '-',
                    $c->created_at ? $c->created_at->format('Y-m-d') : '-',
                ]);
            }

            $customersWithOrders = Customer::whereHas('orders', fn($q) => $q->where('cashier_id', Auth::id())->where('status', '!=', 'refunded'))
                ->with(['orders' => function ($q) {
                    $q->where('cashier_id', Auth::id())
                        ->where('status', '!=', 'refunded')
                        ->with('items');
                }])
                ->get();

            fputcsv($file, []);
            fputcsv($file, ['Total Customers', $customers->count()]);
            fputcsv($file, ['Total Revenue', '$' . number_format($customers->sum('total_spent'), 2)]);

            foreach ($customersWithOrders as $c) {
                foreach ($c->orders as $order) {
                    foreach ($order->items as $item) {
                        fputcsv($file, [
                            $c->name,
                            $order->order_number,
                            $order->created_at->format('Y-m-d H:i'),
                            $item->name,
                            $item->quantity,
                            $item->price,
                            $item->total,
                        ]);
                    }
                }
            }
            fclose($file);
        };

        ActivityService::log('Customer_exported', 'exported Customer report (CSV)', 'Customer', 'info');

        return response()->stream($callback, 200, $headers);
    }
}
