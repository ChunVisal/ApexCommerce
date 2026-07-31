<?php

namespace App\Http\Controllers;

use App\Models\StockRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Helpers\ActivityData;
use Illuminate\Support\Facades\Log;

class StockRequestController extends Controller
{
    public function store(Request $request)
    {
        $productName = $request->product_name ?? Product::find($request->product_id)->name ?? 'Unknown';

        StockRequest::create([
            'cashier_id' => Auth::id(),
            'product_id' => $request->product_id ?: null,
            'product_name' => $request->product_name ?: null,
            'quantity_requested' => $request->quantity,
            'cashier_notes' => $request->note,
            'status' => 'pending',
        ]);

        ActivityData::log(
            'stock_requested',
            "Requested restock of {$request->quantity}x {$productName}",
            'POS',
            'info'
        );

        return response()->json(['message' => 'Request sent to admin']);
    }

    public function bulkProductRequest(Request $request)
    {
        $names = collect($request->items)->map(function ($item) {
            return ($item['quantity'] ?? 1) . 'x ' . ($item['name'] ?? 'Product #' . $item['product_id']);
        })->join(', ');

        foreach ($request->items as $item) {
            StockRequest::create([
                'cashier_id' => Auth::id(),
                'product_id' => $item['product_id'] ?: null,
                'product_name' => $item['name'] ?? null,
                'quantity_requested' => $item['quantity'] ?? 1,
                'cashier_notes' => $item['note'] ?? null,
                'status' => 'pending',
            ]);
        }

        ActivityData::log(
            'stock_requested',
            "Requested restock/new product: {$names}",
            'POS',
            'info'
        );

        return response()->json(['message' => count($request->items) . ' requests sent']);
    }


    public function index()
    {
        $requests = StockRequest::with(['cashier', 'product'])
            ->where('status', 'pending')->latest()->get();
        return view('admin.stock-requests', compact('requests'));
    }
}
