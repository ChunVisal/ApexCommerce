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
    // loads the main POS screen categories, products available to sell
    public function pos(Request $request)
    {
        $cashierId = Auth::id();

        $cashierId = Auth::id();

        // checks a PRODUCT — is it active, and does the cashier still have stock of it?
        $sellableFilter = function ($q) use ($cashierId) {
            $q->where('status', 'active')
                ->whereHas('cashierStocks', function ($sq) use ($cashierId) {
                    $sq->where('cashier_id', $cashierId)->whereRaw('allocated_quantity > sold_quantity');
                });
        };

        // this checks a CASHIER_STOCKS row directly — does IT belong to this cashier and have stock left?
        $hasStockFilter = function ($sq) use ($cashierId) {
            $sq->where('cashier_id', $cashierId)->whereRaw('allocated_quantity > sold_quantity');
        };

        // load categories but only if at least one product that cashier still has stock left to sell
        $categories = Categories::whereHas('products', $sellableFilter)
            ->withCount(['products as products_count' => $sellableFilter])
            ->get();

        // load products with uoms and stock, only active ones this cashier can still sell
        $products = Product::with(['category', 'uoms', 'cashierStocks' => function ($q) use ($cashierId) {
            $q->where('cashier_id', $cashierId);
        }])
            ->where('status', 'active')
            ->whereHas('cashierStocks', $hasStockFilter)  // use RULE 2 here, not RULE 1
            ->get()
            ->map(function ($product) {
                // clean up this product's uom list into a simple array
                $product->uom_list = $product->uoms->map(function ($uom) {
                    return [
                        'id' => $uom->id,
                        'name' => $uom->name,
                        'conversion' => $uom->quantity_per_unit,
                        'price' => (float) $uom->price,
                    ];
                })->values()->toArray();

                // stock rows were already loaded above (with cashierStocks), just read them, no new query
                $stocks = $product->cashierStocks;
                $product->available_stock = $stocks->sum('allocated_quantity') - $stocks->sum('sold_quantity');
                return $product;
            });

        $totalAllocated = $products->sum('available_stock');

        if ($request->ajax) {
            return response()->json(['product' => $products]);
        }


        return view('cashier.pos.index', compact('categories', 'products', 'totalAllocated'));
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
            // check if customer have info inlcude this 3 if not create or skip 
            if ($request->customer && $request->customer['name'] && $request->customer['phone']) {
                // create new customer or find exist by phone number identity 
                $customer = Customer::firstOrCreate(
                    ['phone' => $request->customer['phone']],
                    [
                        'name' => $request->customer['name'],
                        'email' => $request->customer['email'] ?? null,
                        'segment' => 'new',
                    ]
                );
                // grab data existing or newly created and store it
                $customerId = $customer->id;
            }

            // # Generate order number exp: INV-00042
            // sort all orders by newest first, grab the most recent one
            $lastOrder = Order::latest()->first();
            // strip "INV-" and get just the digits, +1 to count up; if no previous order exists, start at 1
            $nextNumber = $lastOrder ? intval(substr($lastOrder->order_number, 4)) + 1 : 1;
            // move '0' to front (STR_PAD_LEFT) $nextNumber with '0' until 5 character long  
            $orderNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            $subtotal = 0;
            // loop through every item in the cart, one at a time
            foreach ($request->items as $item) {
                // find this item's real product record (to get its trusted price from DB)
                $product = Product::find($item['id']);
                // item price * qty, equl and plus mutiple time each items into the $subtotal total
                $subtotal += $product->selling_price * $item['qty'];
            }

            $isVip = false;
            $discount = $request->discount ?? 0;
            $vipDiscount = 0;
            // since set if $customerId = null; skip entire
            if ($customerId) {
                // fetch the full customer record using the id saved earlier
                $customer = Customer::find($customerId);
                // safety check ($customer exists, not null) col had deleted, bypass or glitch proceeds without a VIP discount and if segement is vip 
                if ($customer && $customer->segment === 'vip') {
                    $isVip = true;
                    $vipDiscount = $subtotal * 0.05;
                }
            }

            // get tax number from setting if never change default to 10 then / 100
            $taxRate = Setting::get('tax_rate', 10) / 100;

            $totalDiscount = $discount + $vipDiscount;
            // total discount subtract the discount num from the subtotal first and times $tax
            $tax = ($subtotal - $totalDiscount) * $taxRate;
            $total = $subtotal - $totalDiscount + $tax;

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

                // get selling $item via id
                $cashierStock = CashierStock::where('cashier_id', Auth::id())
                    ->where('product_id', $item['id'])
                    ->first();

                // safety check pattern if stock no null continuous
                if ($cashierStock) {
                    $cashierStock->increment('sold_quantity', $item['qty']);
                }
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

            // if everything in this draft is correct — save it all permanently it all here from DB:begin
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
