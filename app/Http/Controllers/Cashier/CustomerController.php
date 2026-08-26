<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Cashier\CustomerService;
use App\Services\Admin\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = CustomerService::getCustomersWithSegments();
        $summaryCards = CustomerService::getSummaryCards();

        return view('cashier.customers.index', compact('customers', 'summaryCards'));
    }

    public function search(Request $request)
    {
        $customers = Customer::whereHas('orders', function ($q) {
            $q->where('cashier_id', Auth::id());
        })
            ->where('name', 'like', '%' . $request->q . '%')
            ->orWhere('phone', 'like', '%' . $request->q . '%')
            ->orWhere('email', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email',
        ]);

        $lastCustomer = Customer::whereNotNull('code')->orderByDesc('code')->first();
        $nextNumber = $lastCustomer
            ? intval(substr($lastCustomer->code, 5)) + 1
            : 1;
        $customerCode = 'CUST-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'segment' => 'new',
            'code' => $customerCode,
        ]);

        ActivityService::log(
            'customer_created',
            Auth::user()->name . ' added customer: ' . $customer->name,
            'Customers',
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => $customer->name . ' added successfully',
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        ActivityService::log(
            'customer_updated',
            Auth::user()->name . ' updated customer: ' . $customer->name,
            'Customers',
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => $customer->name . ' updated successfully',
            'customer' => $customer,
        ]);
    }

    public function show(int $id)
    {
        $customer = Customer::findOrFail($id);

        $orders = Order::with('payment', 'items')
            ->where('customer_id', $id)
            ->where('cashier_id', Auth::id())
            ->latest()
            ->limit(10)
            ->get();

        $totalOrders = $orders->count();

        // Exclude refunded orders from money calculations
        $nonRefundedOrders = $orders->where('status', '!=', 'refunded');
        $totalSpent = $nonRefundedOrders->sum('total');
        $avgOrder = $nonRefundedOrders->count() > 0
            ? $totalSpent / $nonRefundedOrders->count()
            : 0;

        $customer->total_orders = $totalOrders;
        $customer->total_spent = $totalSpent;
        $customer->avg_order = round($avgOrder, 2);

        return response()->json([
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function export()
    {
        $customers = CustomerService::getCustomersWithSegments();

        $filename = 'customers_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['CUSTOMER LIST']);
            fputcsv($file, ['Name', 'Phone', 'Email', 'Segment', 'Total Orders', 'Total Spent', 'Last Order']);

            foreach ($customers as $c) {
                $totalOrders = $c->orders->count();
                $totalSpent = $c->orders->sum('total');

                fputcsv($file, [
                    $c->name,
                    $c->phone,
                    $c->email ?? '-',
                    $totalOrders >= 6 || $totalSpent >= 5000 ? 'VIP' : ($totalOrders >= 3 || $totalSpent >= 2000 ? 'Regular' : 'New'),
                    $totalOrders,
                    number_format($totalSpent, 2),
                    $c->last_order_at ? Carbon::parse($c->last_order_at)->format('Y-m-d') : '-',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['ORDER DETAILS']);
            fputcsv($file, ['Customer', 'Order Number', 'Date', 'Product', 'Qty', 'Price', 'Total']);

            foreach ($customers as $c) {
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
