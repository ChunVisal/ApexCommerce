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
                $product->available_stock = $stocks->sum('allocated_quantity') - $stocks->sum('sold_quantity') - $stocks->sum('lost_quantity');
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
        // 1. Validate request data
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.uom_id' => 'nullable|exists:product_uoms,id',
            'payment_method' => 'required|in:cash,card,khqr',
            'total' => 'required|numeric|min:0',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 2. Handle customer - create/find only during payment
            $customerId = null;
            $customer = null;
            // fetch the  customer using the id saved earlier if record 
            if ($customerId) {
                $customer = Customer::find($customerId);
            }
            // check if customer have info inlcude this 3 if not create or skip 
            if ($request->customer && $request->customer['name'] && $request->customer['phone']) {
                // create new customer or find exist by phone number identity 

                $lastCustomer = Customer::latest()->first();
                $nextNumber = $lastCustomer && $lastCustomer->code
                    ? intval(substr($lastCustomer->code, 5)) + 1
                    : 1;
                $customerCode = 'CUST-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

                $customer = Customer::firstOrCreate(
                    ['phone' => $request->customer['phone']],
                    [
                        'name' => $request->customer['name'],
                        'email' => $request->customer['email'] ?? null,
                        'segment' => 'new',
                        'code' => $customerCode,
                    ]
                );
                // grab data existing or newly created and store it
                $customerId = $customer->id;
            }

            // 3. Generate order number and calculate financial totals
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
                if (!empty($item['uom_id'])) {
                    $uom = $product->uoms->firstWhere('id', $item['uom_id']);
                    $itemPrice = $uom ? $uom->price : $product->selling_price;
                } else {
                    $itemPrice = $product->selling_price;
                }

                $subtotal += $itemPrice * $item['qty'];
            }

            $isVip = false;
            $discount = $request->discount ?? 0;
            $vipDiscount = 0;

            // safety check ($customer exists, not null) col had deleted, bypass or glitch proceeds without a VIP discount and if segement is vip 
            if ($customer && $customer->segment === 'vip') {
                $isVip = true;
                $vipDiscount = $subtotal * 0.05;
            }

            // get tax number from setting if never change default to 10 then / 100
            $taxRate = Setting::get('tax_rate', 10) / 100;

            $totalDiscount = $discount + $vipDiscount;
            // total discount subtract the discount num from the subtotal first and times $tax
            $net = $subtotal - $totalDiscount;
            $tax = $net * $taxRate;
            $total = $net + $tax;

            // 4. Create main order record
            $order = Order::create([
                'order_number' => $orderNumber,
                'cashier_id' => Auth::id(),
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'net' => $net,
                'discount' => $discount,
                'vip_discount' => $vipDiscount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'completed',
            ]);

            // 5. Create order items + database records and adjusting stock
            foreach ($request->items as $item) {

                // prevent if cashier sale items at the same time, one to wait until the other finishes
                $product = Product::lockForUpdate()->findOrFail($item['id']);

                // same price-lookup logic, repeated here (needs to happen in both loops)
                if (!empty($item['uom_id'])) {
                    $uom = $product->uoms->firstWhere('id', $item['uom_id']);
                    $itemPrice = $uom ? $uom->price : $product->selling_price;
                    $itemConversion = $uom ? $uom->quantity_per_unit : 1;
                    $itemUnitName = $uom ? $uom->name : ($product->base_unit_name ?? 'piece');
                } else {
                    $itemPrice = $product->selling_price;
                    $itemConversion = 1;
                    $itemUnitName = $product->base_unit_name ?? 'piece';
                }

                // TEMPORARY DEBUG
                Log::info('UOM debug', [
                    'sent_uom_id' => $item['uom_id'] ?? 'NOT SENT',
                    'uom_found' => $uom ?? 'NOT FOUND',
                    'itemConversion' => $itemConversion,
                    'item_qty' => $item['qty'],
                ]);

                // stock check now needs to account for conversion (1 Tube = 10 grams of actual stock)
                $actualQtyNeeded = $item['qty'] * $itemConversion;

                // find this cashier's sptock row for the product just sold
                $cashierStock = CashierStock::where('cashier_id', Auth::id())
                    ->where('product_id', $item['id'])
                    ->first();

                // calculate what THIS cashier actually has left to sell
                $cashierRemaining = $cashierStock
                    ? $cashierStock->allocated_quantity - $cashierStock->sold_quantity - $cashierStock->lost_quantity
                    : 0;

                // Prevent Stale data, Directly calling API, Hacker
                if ($cashierRemaining < $actualQtyNeeded) {
                    throw new \Exception('Insufficient stock for: ' . $product->name);
                }

                // Decrease stock
                $product->decrement('stock_quantity', $actualQtyNeeded);

                // Update this cashier's sold_quantity for this product if there is a cashier stock record
                // (sometimes there may not be, e.g. for a product newly added or not yet assigned to cashier)
                if ($cashierStock) {
                    $cashierStock->increment('sold_quantity', $actualQtyNeeded);
                }

                // create record order items
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $itemPrice,
                    'quantity' => $item['qty'],
                    'base_unit' => $itemUnitName,
                    'total' => $itemPrice * $item['qty'],
                ]);
            }

            // 6. Handle payment processing and updates
            // Create for if payment = cash get amount_received if not go total   
            $amountReceived = $request->payment_method === 'cash' ? $request->amount_received : $total;
            // change only if payment = cash get amountReceived - total = change 
            $change = $request->payment_method === 'cash' ? max(0, $amountReceived - $total) : 0;
            // max(0, never let this go below zero, default is 0

            Payment::create([
                'order_id' => $order->id,
                'method' => $request->payment_method,
                'amount' => $total,
                'amount_received' => $amountReceived,
                'change' => $change,
                'status' => 'completed',
            ]);

            // Update customer stats
            if ($customer) {
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

            // 7. Commit transaction and return response
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
