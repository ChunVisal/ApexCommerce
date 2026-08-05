<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierStock;
use App\Models\Categories;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Product;
use App\Services\Admin\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function pos(Request $request)
    {

        $cashierId = Auth::id();

        $categories = Categories::whereHas('products', function ($q) use ($cashierId) {
            $q->where('status', 'active')
                ->whereHas('cashierStocks', function ($sq) use ($cashierId) {
                    $sq->where('cashier_id', $cashierId)
                        ->whereRaw('allocated_quantity > sold_quantity');
                });
        })->withCount(['products as products_count' => function ($q) use ($cashierId) {
            $q->where('status', 'active')
                ->whereHas('cashierStocks', function ($sq) use ($cashierId) {
                    $sq->where('cashier_id', $cashierId)
                        ->whereRaw('allocated_quantity > sold_quantity');
                });
        }])->get();

        $products = Product::with('category', 'uoms')
            ->where('status', 'active')
            ->whereHas('cashierStocks', function ($q) use ($cashierId) {
                $q->where('cashier_id', $cashierId)
                    ->whereRaw('allocated_quantity > sold_quantity');
            })
            ->get()
            ->map(function ($product) use ($cashierId) {
                $product->uom_list = $product->uoms->map(function ($uom) {
                    return [
                        'id' => $uom->id,
                        'name' => $uom->name,
                        'conversion' => $uom->quantity_per_unit,
                        'price' => (float) $uom->price,
                    ];
                })->values()->toArray();
                $stocks = $product->cashierStocks()->where('cashier_id', $cashierId)->get();
                $product->available_stock = $stocks->sum('allocated_quantity') - $stocks->sum('sold_quantity');
                return $product;
            });

        if ($request->ajax) {
            return response()->json(['product' => $products]);
        }

        $totalAllocated = $products->sum('available_stock');
        $categoryCounts = [];
        foreach ($categories as $cat) {
            $categoryCounts[$cat->id] = $cat->products_count;
        }

        return view('cashier.pos.index', compact('categories', 'products', 'categoryCounts', 'totalAllocated'));
    }

    public function checkout(Request $request)
    {
        // Handle checkout logic
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,khqr',
            'total' => 'required|numeric|min:0',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Handle customer - create/find only during payment
            $customerId = null;
            if ($request->customer && $request->customer['name'] && $request->customer['phone']) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $request->customer['phone']],
                    [
                        'name' => $request->customer['name'],
                        'email' => $request->customer['email'] ?? null,
                        'segment' => 'new',
                    ]
                );
                $customerId = $customer->id;
            }

            // 1. Generate order number
            $lastOrder = Order::latest()->first();
            $nextNumber = $lastOrder ? intval(substr($lastOrder->order_number, 4)) + 1 : 1;
            $orderNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            $isVip = false;


            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['id']);
                $subtotal += $product->selling_price * $item['qty'];
            }

            $discount = $request->discount ?? 0;
            $vipDiscount = 0;
            if ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer && $customer->segment === 'vip') {
                    $isVip = true;
                    $vipDiscount = $subtotal * 0.05;
                }
            }

            $taxRate = Setting::get('tax_rate', 10) / 100;

            $totalDiscount = $discount + $vipDiscount;
            $tax = ($subtotal - $totalDiscount) * $taxRate;
            $total = $subtotal - $totalDiscount  + $tax;

            $order = Order::create([
                'order_number' => $orderNumber,
                'cashier_id' => Auth::id(),
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'vip_discount' => $vipDiscount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'completed',
            ]);

            $cashierStock = CashierStock::where('cashier_id', Auth::id())
                ->where('product_id', $item['id'])
                ->first();

            if ($cashierStock) {
                $cashierStock->increment('sold_quantity', $item['qty']);
            }

            // 4. Create order items + Update stock
            foreach ($request->items as $item) {

                $product = Product::lockForUpdate()->findOrFail($item['id']);

                // Check stock
                if ($product->stock_quantity < $item['qty']) {
                    throw new \Exception('Insufficient stock for: ' . $product->name);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->selling_price,
                    'quantity' => $item['qty'],
                    'base_unit' => $item['base_unit'] ?? null,
                    'total' => $product->selling_price * $item['qty'],
                ]);

                // Decrease stock
                $product->decrement('stock_quantity', $item['qty']);
            }

            // 5. Create payment
            $amountReceived = $request->payment_method === 'cash' ? $request->amount_received : $total;
            $change = $request->payment_method === 'cash' ? max(0, $amountReceived - $total) : 0;

            Payment::create([
                'order_id' => $order->id,
                'method' => $request->payment_method,
                'amount' => $total,
                'amount_received' => $amountReceived,
                'change' => $change,
                'status' => 'completed',
            ]);

            // Update customer stats
            if ($customerId) {
                $customer = Customer::find($customerId);
                $customer->increment('total_orders');
                $customer->increment('total_spent', $total);
                $customer->update(['last_order_at' => now()]);

                // Update segment
                if ($customer->total_orders >= 6 || $customer->total_spent >= 5000) {
                    $customer->update(['segment' => 'vip']);
                } elseif ($customer->total_orders >= 3 || $customer->total_spent >= 2000) {
                    $customer->update(['segment' => 'regular']);
                }
            }

            DB::commit();

            ActivityService::log(
                'order_completed',
                "Sale {$orderNumber} completed - \${$total} - " . count($request->items) . " items",
                'POS',
                'success'
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment completed',
                'order' => [
                    'order_number' => $order->order_number,
                    'total' => $total,
                    'change' => $change,
                    'discount' => $discount,
                    'is_vip' => $isVip,
                    'vip_discount' => $vipDiscount,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
