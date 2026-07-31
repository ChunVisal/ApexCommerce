<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\CashierStock;
use App\Models\Product;
use App\Models\StockRequest;
use App\Helpers\ActivityData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {

        $orders = Order::with(['items', 'payment', 'customer'])
            ->where('cashier_id', Auth::id())
            ->when($request->filter, function ($q) use ($request) {
                $filter = $request->filter;
                if ($filter === 'today') {
                    $q->whereDate('created_at', Carbon::today());
                } elseif ($filter === 'yesterday') {
                    $q->whereDate('created_at', Carbon::yesterday());
                } elseif ($filter === 'last_7_days') {
                    $q->whereDate('created_at', '>=', Carbon::now()->subDays(6));
                } elseif ($filter === 'last_30_days') {
                    $q->whereDate('created_at', '>=', Carbon::now()->subDays(29));
                }
            })
            ->when($request->payment && $request->payment !== 'all', function ($q) use ($request) {
                $q->whereHas('payment', fn($p) => $p->where('method', $request->payment));
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('order_number', 'like', '%' . $request->search . '%')
                        ->orWhereHas('customer', fn($c) => $c->where('name', 'like', '%' . $request->search . '%'))
                        ->orWhereHas('items', fn($i) => $i->where('name', 'like', '%' . $request->search . '%'));
                });
            })
            ->latest()
            ->get();

        if ($request->has('ajax')) {
            return response()->json(['orders' => $orders->items()]);
        }


        $currentFilter = $request->filter ?? 'all_time';
        $filterLabels = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'all_time' => 'All Time',
        ];
        $selectedFilter = $filterLabels[$currentFilter] ?? 'All Time';


        $todayTotal = Order::where('cashier_id', Auth::id())
            ->whereDate('created_at', today())->sum('total');
        $todayCount = Order::where('cashier_id', Auth::id())
            ->whereDate('created_at', today())->count();
        $todayAvg = $todayCount > 0 ? $todayTotal / $todayCount : 0;

        // Payment breakdown today
        $todayCash = Payment::whereHas('order', fn($q) => $q->where('cashier_id', Auth::id())->whereDate('created_at', today()))
            ->where('method', 'cash')->sum('amount');
        $todayCard = Payment::whereHas('order', fn($q) => $q->where('cashier_id', Auth::id())->whereDate('created_at', today()))
            ->where('method', 'card')->sum('amount');
        $todayKhqr = Payment::whereHas('order', fn($q) => $q->where('cashier_id', Auth::id())->whereDate('created_at', today()))
            ->where('method', 'khqr')->sum('amount');

        return view('cashier.orders', compact('orders', 'todayTotal', 'todayCount', 'selectedFilter', 'todayAvg', 'todayCash', 'todayCard', 'todayKhqr'));
    }

    public function show($id)
    {
        $order = Order::with(['items', 'payment', 'customer'])
            ->where('cashier_id', Auth::id())
            ->findOrFail($id);

        return response()->json(['order' => $order]);
    }

    public function export(Request $request)
    {
        $orders = Order::with(['items', 'payment', 'customer'])
            ->where('cashier_id', Auth::id())
            ->when($request->search, function ($q) { /* same search */
            })
            ->when($request->range && $request->range !== 'all', function ($q) { /* same date filter */
            })
            ->when($request->payment && $request->payment !== 'all', function ($q) { /* same payment filter */
            })
            ->latest()
            ->get();

        $filename = 'orders_' . now()->format('Y_m_d') . '.csv';

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order Number', 'Customer', 'Phone', 'Items', 'Total', 'Payment', 'Date']);
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->customer->name ?? 'Walk-in',
                    $order->customer->phone ?? '-',
                    $order->items->sum('quantity'),
                    number_format($order->total, 2),
                    $order->payment->method ?? '-',
                    $order->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function refund(Request $request, $id)
    {
        $order = Order::with('items')->where('cashier_id', Auth::id())->findOrFail($id);
        $order_number = $order->order_number; // get the order_number here

        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Order already refunded', 'order_number' => $order_number], 400);
        }

        DB::transaction(function () use ($order, $request, $order_number) {
            // Mark order as refunded
            $order->update([
                'status' => 'refunded',
                'refund_reason' => $request->reason,
                'refunded_at' => now(),
            ]);

            // Restock items if checkbox checked
            if ($request->restock) {
                foreach ($order->items as $item) {
                    Product::find($item->product_id)->increment('stock_quantity', $item->quantity);

                    // Also restock cashier allocation
                    $cashierStock = CashierStock::where('cashier_id', $order->cashier_id)
                        ->where('product_id', $item->product_id)
                        ->first();
                    if ($cashierStock) {
                        $cashierStock->decrement('sold_quantity', $item->quantity);
                    }

                    $stockMovement = StockMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'balance' => Product::find($item->product_id)->stock_quantity,
                        'reference' => 'REF-' . $order_number,
                        'reason' => 'Refund: ' . $request->reason,
                        'user_id' => Auth::id(),
                    ]);

                    // After refund StockMovement, create StockRequest to notify cashier of refund restock
                    StockRequest::create([
                        'cashier_id' => $order->cashier_id,
                        'product_id' => $item->product_id,
                        'quantity_requested' => $item->quantity,
                        'quantity_approved' => $item->quantity,
                        'status' => 'refunded',
                        'cashier_notes' => 'Order ' . $order->order_number . ' refunded: ' . $request->reason,
                        'approved_by' => Auth::id(),
                        'seen_at' => null,
                    ]);

                    ActivityData::log(
                        'order_refunded',
                        "Order {$order_number} refunded - " . Auth::user()->name,
                        'Orders',
                        'danger'
                    );
                }
            }
        });

        return response()->json(['message' => 'Order refunded successfully', 'order_number' => $order_number]);
    }
}
