<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\StockActivity;
use App\Models\Product;
use App\Services\Admin\ActivityService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class StockActivityController extends Controller
{
    public function store(Request $request)
    {
        // find product name to display -> product_name if it was sent directly, ?? look up the product by ID and grab its real name
        $productName = $request->product_name ?? Product::find($request->product_id)->name ?? 'Unknown';

        StockActivity::create([
            'cashier_id' => Auth::id(),
            'product_id' => $request->product_id ?: null,
            'product_name' => $request->product_name ?: null,
            'quantity_requested' => $request->quantity,
            'cashier_notes' => $request->note,
            'status' => 'pending',
        ]);

        ActivityService::log(
            'stock_requested',
            "Requested restock of {$request->quantity}x {$productName}",
            'Product',
            'info'
        );

        return response()->json(['success' => true, 'message' => 'Request sent to admin']);
    }

    public function bulkProductRequest(Request $request)
    {
        // collect the items Collection name and qty combine into a string
        $names = collect($request->items)->map(function ($item) {
            return ($item['quantity'] ?? 1) . 'x ' . ($item['name'] ?? 'Product #' . $item['product_id']);
        })->join(', ');

        // loop each items product had created 
        foreach ($request->items as $item) {
            StockActivity::create([
                'cashier_id' => Auth::id(),
                'product_id' => $item['product_id'] ?: null,
                'product_name' => $item['name'] ?? null,
                'quantity_requested' => $item['quantity'] ?? 1,
                'cashier_notes' => $item['note'] ?? null,
                'status' => 'pending',
            ]);
        }

        ActivityService::log(
            'stock_requested',
            "Requested restock/new product: {$names}",
            'Product',
            'info'
        );

        return response()->json(['success' => true, 'message' => count($request->items) . ' requests sent']);
    }
}
