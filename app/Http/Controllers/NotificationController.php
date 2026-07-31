<?php

namespace App\Http\Controllers;

use App\Models\CashierStock;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockRequest;
use App\Models\User;
use App\Helpers\ActivityData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 3;

        $stockRequests = StockRequest::with(['cashier', 'product'])
            ->whereIn('status', ['pending', 'loss_reported', 'refunded'])
            ->latest()
            ->get()
            ->groupBy(function ($req) {
                if ($req->created_at->isToday()) return 'Today';
                if ($req->created_at->isYesterday()) return 'Yesterday';
                return $req->created_at->format('l, M d, Y');
            });

        $totalGroups = $stockRequests->count();
        $stockRequests = $stockRequests->slice(0, $perPage);
        $hasMore = $totalGroups > $perPage;

        // Handle ajax request for loading more notification groups
        if ($request->ajax()) {
            return view('admin.partials.notifications.list', compact('stockRequests', 'hasMore', 'perPage', 'totalGroups'))->render();
        }

        $pendingCount = StockRequest::whereIn('status', ['pending', 'loss_reported', 'refunded'])->whereNull('seen_at')->count();
        return view('admin.notifications', compact('stockRequests', 'pendingCount', 'hasMore', 'perPage', 'totalGroups'));
    }

    public function cashierIndex(Request $request)
    {
        $perPage = $request->per_page ?? 3;
        // Mark all as seen
        StockRequest::where('cashier_id', Auth::id())
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        $notifications = StockRequest::with(['product', 'approver'])
            ->where('cashier_id', Auth::id())
            ->whereIn('status', ['pending', 'approved', 'rejected', 'on_hold'])
            ->latest()
            ->get()
            ->groupBy(function ($req) {
                if ($req->created_at->isToday()) return 'Today';
                if ($req->created_at->isYesterday()) return 'Yesterday';
                return $req->created_at->format('l, M d, Y');
            });

        $totalGroups = $notifications->count();
        $notifications = $notifications->slice(0, $perPage);
        $hasMore = $totalGroups > $perPage;

        if ($request->ajax) {
            return view('cashier.partials.notifications.list', compact('notifications', 'hasMore', 'perPage', 'totalGroups'))->render();
        }
        return view('cashier.notifications', compact('notifications', 'perPage', 'hasMore', 'totalGroups'));
    }

    public function approve(Request $request, $id)
    {

        $stockRequest = StockRequest::with('product')->findOrFail($id);

        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'This request was already processed.');
        }

        // New product - no stock, just approve
        if (!$stockRequest->product_id) {
            $stockRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'seen_at' => null,
            ]);
            return back()->with('success', 'Request acknowledged');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = (int) $request->quantity;
        $product = $stockRequest->product;

        if ($product->stock_quantity < $quantity) {
            return back()->with('error', 'Not enough stock! Warehouse has ' . $product->stock_quantity . ' left.');
        }

        DB::transaction(function () use ($stockRequest, $product, $quantity) {
            // decrease admin warehouse
            $product->decrement('stock_quantity', $quantity);

            // increase cashier stock
            $cashierStock = CashierStock::where('cashier_id', $stockRequest->cashier_id)
                ->where('product_id', $stockRequest->product_id)
                ->first();

            if ($cashierStock) {
                $cashierStock->increment('allocated_quantity', $quantity);
            } else {
                CashierStock::create([
                    'product_id' => $stockRequest->product_id,
                    'cashier_id' => $stockRequest->cashier_id,
                    'allocated_quantity' => $quantity,
                    'sold_quantity' => 0,
                    'allocated_by' => Auth::id(),
                ]);
            }

            // log movement
            StockMovement::create([
                'product_id' => $stockRequest->product_id,
                'type' => 'out',
                'quantity' => $quantity,
                'balance' => Product::find($stockRequest->product_id)->stock_quantity,
                'reason' => 'Transfer to ' . User::find($stockRequest->cashier_id)->name,
                'reference' => 'DROP-' . str_pad($stockRequest->cashier_id, 3, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi'),
                'user_id' => Auth::id(),
            ]);

            // mark request approved
            $stockRequest->update([
                'status' => 'approved',
                'quantity_approved' => $quantity,
                'approved_by' => Auth::id(),
                'seen_at' => null,
            ]);

            ActivityData::log(
                'request_approved',
                "Approved {$quantity}x {$product->name} for " . $stockRequest->cashier->name,
                'Notifications',
                'success'
            );
        });

        return back()->with('success', 'Stock transferred!');
    }

    public function reject(Request $request, $id)
    {
        $req = StockRequest::findOrFail($id);
        $req->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'dispute_reason' => $request->reason,
            'seen_at' => null,
        ]);

        $productName = $req->product->name ?? $req->product_name ?? 'Unknown';

        ActivityData::log(
            'request_rejected',
            "Rejected request for {$req->quantity_requested}x {$productName} - Reason: {$request->reason}",
            'Notifications',
            'warning'
        );

        return back()->with('success', 'Request rejected');
    }

    public function markAllRead()
    {
        StockRequest::whereIn('status', ['pending', 'loss_reported'])
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markSingle($id)
    {
        StockRequest::where('id', $id)->update(['seen_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function returnStock(Request $request)
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

    public function markSingleRead($id)
    {
        StockRequest::where('id', $id)
            ->where('cashier_id', Auth::id()) // add this
            ->update(['seen_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markRead()
    {
        StockRequest::where('cashier_id', Auth::id())
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        return response()->json(['success' => true]);
    }
}
