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

        $products = ProductService::getCashierProducts($cashierId);

        if ($request->ajax()) {
            return response()->json(['products' => $products]);
        }

        // request all products from warehouse show only active by name
        $allProducts = Product::where('status', 'active')->orderBy('name', 'asc')->get();

        $categories = ProductService::getCashierCategories($cashierId);

        $summaryCards = ProductService::getSummaryCards();

        return view('cashier.products.index', compact('products', 'summaryCards', 'categories', 'allProducts'));
    }

    public function reportLoss(Request $request)
    {
        // return one items only not a list
        $cashierStock = CashierStock::where('cashier_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        // prevent Double-submission, user Direct API tampering bypasses
        if (!$cashierStock) {
            return response()->json(['message' => 'No cashier stock found for this product.'], 422);
        }

        // Only use cashier's own allocated stock for loss
        $remaining = $cashierStock->allocated_quantity - $cashierStock->sold_quantity;
        if ($request->quantity > $remaining) {
            return response()->json(['message' => 'Cannot report more than available in your allocated stock'], 422);
        }

        // total of cashierStock - ( remaining = allocated_quantity - sold_sty ) 
        $cashierStock->decrement('allocated_quantity', $request->quantity);

        // then total of cashierStock = remaning qty - quantity 
        $newRemaining = $remaining - $request->quantity;

        StockMovement::create([
            'product_id' => $request->product_id,
            'type' => 'out',
            'quantity' => $request->quantity,
            'balance' => $newRemaining,
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
