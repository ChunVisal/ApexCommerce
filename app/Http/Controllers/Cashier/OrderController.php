<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\CashierStock;
use App\Models\Product;
use App\Models\StockActivity;
use App\Services\Admin\ActivityService;
use App\Services\Cashier\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $request->validate([
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.restock' => 'required|boolean',
        ]);

        // find items order for only belong to cashier or fail message
        $order = Order::with('items')->where('cashier_id', Auth::id())->findOrFail($id);
        $order_number = $order->order_number; // get the order_number invoice here

        // Prevent refunding items that are already refunded, or don't belong to this order
        foreach ($request->items as $refundItem) {
            $item = $order->items->firstWhere('id', $refundItem['order_item_id']);

            if (!$item) {
                return response()->json(['message' => 'Invalid item for this order'], 400);
            }

            $alreadyRefunded = $item->refunded_quantity ?? 0;
            $remainingRefundable = $item->quantity - $alreadyRefunded;

            if ($refundItem['quantity'] > $remainingRefundable) {
                return response()->json(['success' => false, 'message' => $item->name . ' only has ' . $remainingRefundable . ' unit(s) left to refund'], 400);
            }
        }

        // groups multiple database changes together, so they either all succeed or all fail if one fail reset all
        try {
            DB::beginTransaction();

            // loops mutiply through each one, one at a time then increase qty
            foreach ($request->items as $refundItem) {
                // then restock items to specfic cashier_id allocation
                $item = $order->items->firstWhere('id', $refundItem['order_item_id']);
                $restock = $refundItem['restock'];   // THIS item's own restock choice
                $refundQty = $refundItem['quantity'];

                $cashierStock = CashierStock::where('cashier_id', $order->cashier_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($cashierStock) {
                    // if restock increase stock qty
                    $cashierStock->decrement('sold_quantity', $refundQty);

                    // prevent maybe someone edits the database by hand, some new feature bug
                    if (!$restock) {
                        // Item is broken/lost — track it separately, allocated stays the same
                        $cashierStock->increment('lost_quantity', $item->quantity);
                    }
                }

                $movementType = $restock ? 'in' : 'out';
                $movementReason = $restock
                    ? 'Refund (restocked): ' . $request->reason
                    : 'Refund (lost/broken): ' . $request->reason;

                // refresh so allocated/sold/lost reflect the increment/decrement above
                $cashierStock?->refresh();

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'type' => $movementType,
                    'quantity' => $refundQty,
                    'balance' => $cashierStock
                        ? $cashierStock->allocated_quantity - $cashierStock->sold_quantity - $cashierStock->lost_quantity
                        : 0,
                    'reference' => 'REF-' . $order_number,
                    'reason' => $movementReason,
                    'user_id' => Auth::id(),
                ]);


                // Track the NEW total refunded quantity for this item
                $newRefundedTotal = ($item->refunded_quantity ?? 0) + $refundQty;
                // NEW — mark THIS specific item as refunded, so it can't be refunded again later
                $item->update([
                    'refunded_quantity' => $newRefundedTotal,
                    'is_refunded' => $newRefundedTotal >= $item->quantity,
                    'refund_type' => $restock ? 'restock' : 'broken',
                ]);

                // After refund StockMovement, create StockRequest to notify cashier of refund restock
                if (!$restock) {
                    StockActivity::create([
                        'cashier_id' => $order->cashier_id,
                        'product_id' => $item->product_id,
                        'quantity_requested' => $refundQty,
                        'quantity_approved' => $item->quantity,
                        'status' => 'refunded',
                        'cashier_notes' => 'Order ' . $order->order_number . ' refunded: ' . $request->reason,
                        'approved_by' => Auth::id(),
                        'seen_at' => null,
                    ]);
                }

                ActivityService::log(
                    'order_refunded',
                    "Order {$order_number} refunded - " . Auth::user()->name,
                    'Orders',
                    'warning'
                );
            }

            // Determine the order's OVERALL status after this refund
            $totalItems = $order->items->count();
            $refundedItems = $order->items()->where('is_refunded', true)->count();

            // Recalculate remaining totals based on what's LEFT (non-refunded items only)
            $remainingSubtotal = $order->items->sum(function ($item) {
                $unrefundedQty = $item->quantity - $item->refunded_quantity;
                $unitPrice = $item->price;
                return $unrefundedQty * $unitPrice;
            });

            $taxRate = \App\Models\Setting::get('tax_rate', 10) / 100;
            $remainingDiscount = $order->subtotal > 0 ? ($order->discount / $order->subtotal) * $remainingSubtotal : 0;
            $remainingVipDiscount = $order->subtotal > 0 ? ($order->vip_discount / $order->subtotal) * $remainingSubtotal : 0;
            $remainingNet = $remainingSubtotal - $remainingDiscount - $remainingVipDiscount;
            $remainingTax = $remainingNet * $taxRate;
            $remainingTotal = $remainingNet + $remainingTax;

            $order->update([
                'status' => $refundedItems >= $totalItems ? 'refunded' : 'partially_refunded',
                'refund_reason' => $request->reason,
                'refunded_at' => now(),
                'subtotal' => $remainingSubtotal,
                'discount' => $remainingDiscount,
                'vip_discount' => $remainingVipDiscount,
                'net' => $remainingNet,
                'tax' => $remainingTax,
                'total' => $remainingTotal,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order ' . $order_number . ' refunded successfully',
                'order_number' => $order_number,
                'total' => $order->total,
                'refunded_at' => $order->refunded_at,

            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ], 500);
        }
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
