<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::whereHas('orders', function ($q) {
            $q->where('cashier_id', Auth::id())
                ->where('status', '!=', 'refunded');
        })
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            })
            ->withCount(['orders as total_orders' => function ($q) {
                $q->where('cashier_id', Auth::id())
                    ->where('status', '!=', 'refunded');
            }])
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('cashier_id', Auth::id())
                    ->where('status', '!=', 'refunded');
            }], 'total')
            ->orderBy('last_order_at', 'desc')
            ->get()
            ->map(function ($customer) {
                // Calculate segment per cashier
                if ($customer->total_orders >= 6 || $customer->total_spent >= 5000) {
                    $customer->segment = 'vip';
                } elseif ($customer->total_orders >= 3 || $customer->total_spent >= 2000) {
                    $customer->segment = 'regular';
                } else {
                    $customer->segment = 'new';
                }

                return $customer;
            });

        return view('cashier.customers', compact('customers'));
    }

    public function adminIndex(Request $request)
    {
        $customers = Customer::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            })
            ->withCount(['orders as total_orders' => fn($q) => $q->where('status', '!=', 'refunded')])
            ->withSum(['orders as total_spent' => fn($q) => $q->where('status', '!=', 'refunded')], 'total')
            ->orderBy('last_order_at', 'desc')
            ->get();

        return view('admin.customers', compact('customers'));
    }

    public function search(Request $request)
    {
        $customers = Customer::whereHas('orders', function ($q) {
            $q->where('cashier_id', Auth::id());
        })
            ->where('name', 'like', '%' . $request->q . '%')
            ->orWhere('phone', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    public function adminShow($id)
    {
        $customer = Customer::findOrFail($id);

        $orders = Order::with('payment', 'items')
            ->where('customer_id', $id)
            ->latest()
            ->limit(20)
            ->get();

        $totalOrders = $orders->count();

        // Exclude refunded orders from money calculations
        $nonRefundedOrders = $orders->where('status', '!=', 'refunded');
        $totalSpent = $nonRefundedOrders->sum('total');
        $avgOrder = $nonRefundedOrders->count() > 0
            ? $totalSpent / $nonRefundedOrders->count()
            : 0;

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

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'phone' => 'required|unique:customers']);
        $customer = Customer::create($request->all());

        return response()->json(['customer' => $customer]);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->all());

        return response()->json(['customer' => $customer]);
    }

    public function show($id)
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

    public function getOrder($customerId, $orderId)
    {
        $order = Order::with(['items', 'payment', 'customer'])
            ->where('customer_id', $customerId)
            ->findOrFail($orderId);

        return response()->json(['order' => $order]);
    }

    public function export()
    {
        $userId = Auth::id();

        $customers = Customer::whereHas('orders', fn($q) => $q->where('cashier_id', $userId))
            ->with(['orders' => function ($q) use ($userId) {
                $q->where('cashier_id', $userId)->with('items');
            }])
            ->get();

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

        return response()->stream($callback, 200, $headers);
    }

    public function adminExport()
    {
        $customers = Customer::withCount('orders as total_orders')
            ->withSum('orders as total_spent', 'total')
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
                    $c->created_at->format('Y-m-d'),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Total Customers', $customers->count()]);
            fputcsv($file, ['Total Revenue', '$' . number_format($customers->sum('total_spent'), 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
