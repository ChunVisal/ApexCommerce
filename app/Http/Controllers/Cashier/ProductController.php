<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Services\Cashier\ProductService;
use App\Models\Categories;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\CashierStock;
use App\Models\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $cashierId = Auth::id();

        $products = Product::with(['category', 'uoms', 'cashierStocks' => function ($q) use ($cashierId) {
            $q->where('cashier_id', $cashierId);
        }])
            ->where('status', 'active')
            ->whereHas('cashierStocks', function ($q) use ($cashierId) {
                $q->where('cashier_id', $cashierId);
            })
            ->get()
            ->map(function ($product) {
                $product->allocated = $product->cashierStocks->sum('allocated_quantity');
                $product->sold = $product->cashierStocks->sum('sold_quantity');
                $product->remaining = $product->allocated - $product->sold;
                $product->revenue = $product->sold * $product->selling_price;
                $product->last_drop = $product->cashierStocks->max('created_at');
                $product->category_name = $product->category->name ?? '-';
                $product->cashier_remaining;

                $product->uom_list = $product->uoms->map(function ($uom) {
                    return [
                        'id' => $uom->id,
                        'name' => $uom->name,
                        'allocated_quantity' => $uom->quantity_per_unit,
                        'price' => (float) $uom->price,
                        'is_default' => (bool) $uom->is_default,

                    ];
                })->values()->toArray();

                return $product;
            });

        if ($request->ajax) {
            return response()->json(['products' => $products]);
        }

        $allProducts = Product::where('status', 'active')->orderBy('name')->get();

        $categories = Categories::whereHas('products', function ($q) use ($cashierId) {
            $q->whereHas('cashierStocks', fn($sq) => $sq->where('cashier_id', $cashierId));
        })->get();

        foreach ($categories as $category) {
            $category->cashier_remaining = CashierStock::where('cashier_id', $cashierId)
                ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
                ->sum('allocated_quantity');
        }

        $summaryCards = ProductService::getSummaryCards();

        return view('cashier.products.index', compact('products', 'summaryCards', 'categories', 'allProducts'));
    }


    public function reportLoss(Request $request)
    {
        $cashierStock = CashierStock::where('cashier_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cashierStock) {
            return response()->json(['message' => 'No cashier stock found for this product.'], 422);
        }

        // Only use cashier's own allocated stock for returning/loss
        $remaining = $cashierStock->allocated_quantity - $cashierStock->sold_quantity;
        if ($request->quantity > $remaining) {
            return response()->json(['message' => 'Cannot report more than available in your allocated stock'], 422);
        }

        $remaining_before = $cashierStock->allocated_quantity - $cashierStock->sold_quantity;

        $cashierStock->decrement('allocated_quantity', $request->quantity);

        // Refresh from database to get updated value
        $cashierStock->refresh();
        $new_remaining = $remaining_before - $request->quantity;

        StockMovement::create([
            'product_id' => $request->product_id,
            'type' => 'out',
            'quantity' => $request->quantity,
            'balance' => $new_remaining,
            'reason' => 'Loss: ' . $request->reason . ' - ' . Auth::user()->name,
            'reference' => 'LOSS-' . str_pad(StockMovement::where('type', 'out')->where('reason', 'like', 'Loss:%')->count() + 1, 5, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi'),
            'user_id' => Auth::id(),
        ]);

        // Notify admin
        StockRequest::create([
            'cashier_id' => Auth::id(),
            'product_id' => $request->product_id,
            'quantity_requested' => $request->quantity,
            'status' => 'loss_reported',
            'cashier_notes' => $request->reason,
            'seen_at' => null,
        ]);

        return response()->json(['message' => 'Loss reported']);
    }
}
