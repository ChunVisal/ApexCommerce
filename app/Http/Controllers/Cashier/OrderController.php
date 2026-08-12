<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\CashierStock;
use App\Models\Product;
use App\Models\StockRequest;
use App\Services\Admin\ActivityService;
use App\Services\Cashier\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = OrderService::getCashierOrders(Auth::id(), $request->payment, $request->search, $request->filter);

        if ($request->has('ajax')) {
            return response()->json(['orders' => $orders]);
        }

        $summaryCards = OrderService::getSummaryCards();

        $filterLabels = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'all_time' => 'All Time',
        ];
        $selectedFilter = $filterLabels[$request->filter ?? 'all_time'] ?? 'All Time';

        return view('cashier.orders.index', compact('orders', 'summaryCards', 'selectedFilter'));
    }

    public function show(int $id)
    {
        $order = Order::with(['items', 'payment', 'customer'])
            ->where('cashier_id', Auth::id())
            ->findOrFail($id);

        return response()->json(['order' => $order]);
    }

    public function refund(Request $request, int $id)
    {
        // find items order for only belong to cashier or fail message
        $order = Order::with('items')->where('cashier_id', Auth::id())->findOrFail($id);
        $order_number = $order->order_number; // get the order_number invoice here

        // prevent old data, double-click, bypassing API
        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Order already refunded', 'order_number' => $order_number], 400);
        }

        // groups multiple database changes together, so they either all succeed or all fail if one fail reset all
        DB::transaction(function () use ($order, $request, $order_number) {
            // Mark order as refunded
            $order->update([
                'status' => 'refunded',
                'refund_reason' => $request->reason,
                'refunded_at' => now(),
            ]);

            // Restock items if checkbox checked

            // loops mutiply through each one, one at a time then increase qty
            foreach ($order->items as $item) {
                // then restock items to specfic cashier_id allocation
                $cashierStock = CashierStock::where('cashier_id', $order->cashier_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($request->restock) {
                    // if restock increase stock qty
                    Product::find($item->product_id)->increment('stock_quantity', $item->quantity);

                    // prevent maybe someone edits the database by hand, some new feature bug
                    if ($cashierStock) {
                        $cashierStock->decrement('sold_quantity', $item->quantity);
                    }

                    $movementType = 'in';
                    $movementReason = 'Refund (restocked): ' . $request->reason;
                } else {
                    // ---- Item is broken/lost, does NOT go back to sellable stock ----

                    if ($cashierStock) {
                        // sale is reversed either way
                        $cashierStock->decrement('sold_quantity', $item->quantity);
                        // but the item itself is gone, so it's tracked as lost
                        $cashierStock->increment('lost_quantity', $item->quantity);
                    }

                    $movementType = 'out';
                    $movementReason = 'Refund (lost/broken): ' . $request->reason;
                }

                // refresh so allocated/sold/lost reflect the increment/decrement above
                $cashierStock?->refresh();

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'type' => $movementType,
                    'quantity' => $item->quantity,
                    'balance' => $cashierStock
                        ? $cashierStock->allocated_quantity - $cashierStock->sold_quantity - $cashierStock->lost_quantity
                        : 0,
                    'reference' => 'REF-' . $order_number,
                    'reason' => $movementReason,
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

                ActivityService::log(
                    'order_refunded',
                    "Order {$order_number} refunded - " . Auth::user()->name,
                    'Orders',
                    'warning'
                );
            }
        });

        return response()->json(['message' => 'Order refunded successfully', 'order_number' => $order_number]);
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

        ActivityService::log(
            'order_exported',
            "exported order sale (CSV)",
            'order',
            'info'
        );

        return response()->stream($callback, 200, $headers);
    }
}
