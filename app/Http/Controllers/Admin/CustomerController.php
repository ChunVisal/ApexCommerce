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
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount(['orders as total_orders' => fn($q) => $q->where('status', '!=', 'refunded')])
            ->withSum(['orders as total_spent' => fn($q) => $q->where('status', '!=', 'refunded')], 'total')
            ->orderBy('last_order_at', 'desc')
            ->get()
            ->map(function ($customer) {
                $orders = $customer->total_orders ?? 0;
                $spent = $customer->total_spent ?? 0;

                if ($orders >= 6 || $spent >= 5000) {
                    $customer->segment = 'vip';
                } elseif ($orders >= 3 || $spent >= 2000) {
                    $customer->segment = 'regular';
                } else {
                    $customer->segment = 'new';
                }
                return $customer;
            });

        $summaryCards = CustomerService::getSummaryCards();

        return view('admin.customers.index', compact('customers', 'summaryCards'));
    }

    public function show($id)
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

    public function getOrder($customerId, $orderId)
    {
        $order = Order::with(['items', 'payment', 'customer'])
            ->where('customer_id', $customerId)
            ->findOrFail($orderId);

        return response()->json(['order' => $order]);
    }


    public function export()
    {
        $customers = Customer::withCount(['orders as total_orders' => fn($q) => $q->where('status', '!=', 'refunded')])
            ->withSum(['orders as total_spent' => fn($q) => $q->where('status', '!=', 'refunded')], 'total')
            ->orderBy('last_order_at', 'desc')
            ->get();

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

                $segment = $totalOrders >= 6 || $totalSpent >= 5000 ? 'VIP'
                    : ($totalOrders >= 3 || $totalSpent >= 2000 ? 'Regular' : 'New');

                fputcsv($file, [
                    $c->name,
                    $c->phone,
                    $c->email ?? '-',
                    $segment,
                    $totalOrders,
                    number_format($totalSpent, 2),
                    $c->last_order_at ? Carbon::parse($c->last_order_at)->format('Y-m-d') : '-',
                    $c->created_at ? $c->created_at->format('Y-m-d') : '-',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Total Customers', $customers->count()]);
            fputcsv($file, ['Total Revenue', '$' . number_format($customers->sum('total_spent'), 2)]);

            fclose($file);
        };

        ActivityService::log('Customer_exported', 'exported Customer report (CSV)', 'Customer', 'info');

        return response()->stream($callback, 200, $headers);
    }
}
