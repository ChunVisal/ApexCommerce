<?php

namespace App\Http\Controllers;

use App\Helpers\CashierProductData;
use App\Models\Categories;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\CashierStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierProductController extends Controller
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
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
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

        $summaryCards = CashierProductData::getSummaryCards();

        return view('cashier.products', compact('products', 'summaryCards', 'categories', 'allProducts'));
    }
}
