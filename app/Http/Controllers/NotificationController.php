<?php

namespace App\Http\Controllers;

use App\Models\CashierStock;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockRequest;
use App\Models\User;
use App\Models\Order;
use App\Services\Admin\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // how many date-groups to show per page (default 3 if not specified)
        $perPage = $request->per_page ?? 3;

        // fetch pending/loss/refund requests, preload cashier and product info
        $stockRequests = $this->groupByDate(
            StockRequest::with(['cashier', 'product'])
                ->whereIn('status', ['pending', 'loss_reported', 'refunded'])
                ->latest()
                ->get()
        );

        // count how many date-groups exist total
        $totalGroups = $stockRequests->count();
        // keep only the first $perPage groups, discard the rest for this response
        $stockRequests = $stockRequests->slice(0, $perPage);
        $hasMore = $totalGroups > $perPage;

        // AJAX request (e.g. "Load More" click) — return just the HTML fragment, not a full page
        if ($request->ajax()) {
            return view('admin.notifications.list-messages', compact('stockRequests', 'hasMore', 'perPage', 'totalGroups'))->render();
        }

        // count unseen notifications (seen_at still null) — likely powers a badge/bell icon count
        $pendingCount = StockRequest::whereIn('status', ['pending', 'loss_reported', 'refunded'])->whereNull('seen_at')->count();

        // full page load — includes pendingCount for the notification badge
        return view('admin.notifications.index', compact('stockRequests', 'pendingCount', 'hasMore', 'perPage', 'totalGroups'));
    }

    public function cashierIndex(Request $request)
    {
        $perPage = $request->per_page ?? 3;
        // Mark all as seen
        StockRequest::where('cashier_id', Auth::id())
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        $notifications = $notifications = $this->groupByDate(
            StockRequest::with(['product', 'approver'])
                ->where('cashier_id', Auth::id())
                ->whereIn('status', ['pending', 'approved', 'rejected', 'on_hold'])
                ->latest()
                ->get()
        );

        $totalGroups = $notifications->count();
        $notifications = $notifications->slice(0, $perPage);
        $hasMore = $totalGroups > $perPage;

        if ($request->ajax()) {
            return view('cashier.notifications.list-messages', compact('notifications', 'hasMore', 'perPage', 'totalGroups'))->render();
        }
        return view('cashier.notifications.index', compact('notifications', 'perPage', 'hasMore', 'totalGroups'));
    }

    public function approve(Request $request, $id)
    {

        $stockRequest = StockRequest::with('product')->findOrFail($id);

        // prevent old data, double-click
        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'This request was already processed.');
        }

        // Check if product is ull, product not eixst  
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
        // grabs the already-preloaded Product record (name, price, stock_quantity) linked to this request,
        $product = $stockRequest->product;

        if ($product->stock_quantity < $quantity) {
            return back()->with('error', 'Not enough stock! Warehouse has ' . $product->stock_quantity . ' left.');
        }

        DB::beginTransaction();
        // decrease admin warehouse
        $product->decrement('stock_quantity', $quantity);

        // increase cashier stock
        $cashierStock = CashierStock::where('cashier_id', $stockRequest->cashier_id)
            ->where('product_id', $stockRequest->product_id)
            ->first();

        // check if cashier already has product stock row 
        if ($cashierStock) {
            $cashierStock->increment('allocated_quantity', $quantity);
            // else if product stock not exist create new
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

        ActivityService::log(
            'request_approved',
            "Approved {$quantity}x {$product->name} for " . $stockRequest->cashier->name,
            'Notifications',
            'success'
        );

        DB::commit();

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

        // get $productName exist data else new products. Unkown prevent hacker 
        $productName = $req->product->name ?? $req->product_name ?? 'Unknown';

        ActivityService::log(
            'request_rejected',
            "Rejected request for {$req->quantity_requested}x {$productName} - Reason: {$request->reason}",
            'Notifications',
            'warning'
        );

        return back()->with('success', 'Request rejected');
    }

    // Admin
    public function adminMarkAllRead()
    {
        StockRequest::whereIn('status', ['pending', 'loss_reported', 'refunded'])
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function adminMarkSingleRead($id)
    {
        // mark-read specific one via id
        StockRequest::where('id', $id)->update(['seen_at' => now()]);
        return response()->json(['success' => true]);
    }

    // Cashier
    public function cashierMarkAllRead()
    {
        StockRequest::where('cashier_id', Auth::id())
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function cashierMarkSingleRead($id)
    {
        StockRequest::where('id', $id)
            ->where('cashier_id', Auth::id())
            ->update(['seen_at' => now()]);
        return response()->json(['success' => true]);
    }

    private function groupByDate($requests)
    {
        return $requests->groupBy(function ($req) {
            if ($req->created_at->isToday()) return 'Today';
            if ($req->created_at->isYesterday()) return 'Yesterday';
            return $req->created_at->format('l, M d, Y');
        });
    }
}
