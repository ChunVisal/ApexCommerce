<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashierStock;
use App\Models\Categories;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockActivity;
use App\Models\User;
use App\Services\Admin\ActivityService;
use App\Services\Admin\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /* =========================================================================
     | 1. PRIMARY PAGE VIEWS
     | ========================================================================= */

    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->get();

        $categories = Categories::withSum('products as total_stock', 'stock_quantity')->get();

        $cashiers = User::where('role', 'cashier')->get();
        $cashierStocks = CashierStock::select(['cashier_id', 'product_id', 'allocated_quantity', 'sold_quantity', 'lost_quantity'])->get();

        $summaryCards = InventoryService::getSummaryCards();
        $trend = InventoryService::getMovementTrend($request);

        if ($request->ajax()) {
            return response()->json($products);
        }

        return view('admin.inventory.index', compact('products', 'categories', 'summaryCards', 'trend', 'cashiers', 'cashierStocks'));
    }

    public function movements(Request $request)
    {
        $start = $request->start_date
            ? Carbon::parse($request->start_date)
            : now()->subDays(14);
        $end = $request->end_date
            ? Carbon::parse($request->end_date)
            : now();

        $movements = StockMovement::with(['product.category', 'user'])
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->latest()
            ->get();

        // mark all currently-unseen movements as seen
        StockMovement::whereNull('seen_at')->update(['seen_at' => now()]);

        if ($request->ajax()) {
            return response()->json([
                'movements' => $movements,
            ]);
        }

        $categories = Categories::orderBy('name')->get();
        $users = User::all();

        return view('admin.stock-movement.index', compact('movements', 'start', 'end', 'categories', 'users'));
    }

    public function movementsCount()
    {
        return response()->json([
            'count' => StockMovement::whereNull('seen_at')->count(),
        ]);
    }

    /* =========================================================================
     | 2. INVENTORY STOCK OPERATIONS
     | ========================================================================= */

    public function adjustStock(Request $request)
    {
        $request->validate([
            'product_code' => 'required|exists:products,code',
            'type' => 'nullable|in:in,out',
            'quantity' => 'nullable|integer|min:0',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        $product = Product::where('code', $request->product_code)->firstOrFail();

        // add referance number total count adjustment + date 
        $reference = match ($request->type) {
            'in' => 'STKIN-' . str_pad(StockMovement::where('type', 'in')->count() + 1, 5, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi'),
            'out' => 'STKOUT-' . str_pad(StockMovement::where('type', 'out')->count() + 1, 5, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi'),
            default => null,
        };

        // Stock quantity change
        if ($request->quantity > 0) {
            if (! $request->reason) {
                return response()->json(['error' => 'Reason is required for stock adjustment'], 422);
            }

            if ($request->type === 'in') {
                $product->increment('stock_quantity', $request->quantity);
            } else {
                if ($product->stock_quantity < $request->quantity) {
                    return response()->json(['error' => 'Not enough stock'], 422);
                }
                $product->decrement('stock_quantity', $request->quantity);
            }

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'balance' => $product->stock_quantity,
                'reason' => $request->reason,
                'reference' => $request->reference ?? $reference,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            ActivityService::log(
                'stock_adjusted',
                "adjusted stock for {$product->name}: {$request->type} {$request->quantity} ({$request->reason})",
                'Inventory',
                $request->type === 'in' ? 'success' : 'warning'
            );
        }

        // Threshold change
        if ($request->low_stock_threshold !== null) {
            $product->update(['low_stock_threshold' => $request->low_stock_threshold]);
        }

        // Status only change (no quantity)
        if ($request->status && $request->quantity == 0) {
            // update activity status
            $product->update(['status' => $request->status]);

            ActivityService::log(
                'product_status_changed',
                "changed {$product->name} status to {$request->status}",
                'Inventory',
                'info'
            );
        }

        return response()->json([
            'success' => true,
            'message' => $product->name . ' updated successfully',
            'new_stock' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold,
            'status' => $product->status,
        ]);
    }

    public function stockDrop(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'cashier_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock! Warehouse has ' . $product->stock_quantity . ' left.',
            ]);
        }

        // Deduct from warehouse FIRST
        $product->decrement('stock_quantity', $request->quantity);

        $cashierName = User::find($request->cashier_id)->name;
        
        // check cashier already have some
        $cashierStock = CashierStock::firstOrCreate(
            [
                'cashier_id' => $request->cashier_id,
                'product_id' => $request->product_id,
            ],
            [
                'allocated_quantity' => 0,
                'sold_quantity' => 0,
                'allocated_by' => Auth::id(),
            ]
        );
        $cashierStock->increment('allocated_quantity', $request->quantity);

        // Create notification for cashier
        StockActivity::create([
            'cashier_id' => $request->cashier_id,
            'product_id' => $request->product_id,
            'quantity_requested' => $request->quantity,
            'quantity_approved' => $request->quantity,
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'seen_at' => null,
        ]);

        // Log stock movement
        StockMovement::create([
            'product_id' => $request->product_id,
            'type' => 'out',
            'quantity' => $request->quantity,
            'balance' => $product->stock_quantity,
            'reason' => 'Transfer to ' . $cashierName,
            'reference' => 'DROP-' . str_pad($request->cashier_id, 3, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi'),
            'user_id' => Auth::id(),
        ]);

        ActivityService::log(
            'stock_transferred',
            "transferred {$request->quantity}x {$product->name} to {$cashierName}",
            'Inventory',
            'success'
        );

        return response()->json([
            'success' => true,
            'message' => 'transfer ' . $product->name . ' to ' . $cashierName . ' successfully',
            'new_stock' => $product->stock_quantity,
            'cashier_allocated' => $cashierStock->allocated_quantity,
        ]);
    }

    /* =========================================================================
     | 3. EXPORT METHODS
     | ========================================================================= */

    public function export(Request $request)
    {
        $products = Product::with('category')->get();
        $movements = StockMovement::whereBetween('created_at', [
            now()->subDays(6)->startOfDay(),
            now()->endOfDay(),
        ])->get();

        $filename = 'inventory_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($products, $movements) {
            $file = fopen('php://output', 'w');

            // ── Section 1: Summary ──
            fputcsv($file, ['INVENTORY SUMMARY']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Products', $products->count()]);
            fputcsv($file, ['Low Stock', $products->filter(fn($p) => $p->stock_quantity > 0 && $p->stock_quantity <= $p->low_stock_threshold)->count()]);
            fputcsv($file, ['Out of Stock', $products->where('stock_quantity', 0)->count()]);
            fputcsv($file, ['Stock Value ($)', number_format($products->sum(fn($p) => $p->stock_quantity * $p->selling_price), 2)]);
            fputcsv($file, []);

            // ── Section 2: Products ──
            fputcsv($file, ['PRODUCT INVENTORY']);
            fputcsv($file, ['Name', 'Code', 'Category', 'Stock', 'Low Stock Threshold', 'Status', 'Selling Price', 'Last Updated']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->code,
                    $product->category->name ?? 'Unassigned',
                    $product->stock_quantity,
                    $product->low_stock_threshold,
                    ucfirst($product->status),
                    '$' . number_format($product->selling_price, 2),
                    $product->updated_at->format('M d, Y H:i'),
                ]);
            }
            fputcsv($file, []);

            // ── Section 3: Stock Movement (Last 7 days) ──
            fputcsv($file, ['STOCK MOVEMENTS (Last 7 Days)']);
            fputcsv($file, ['Date', 'Stock In', 'Stock Out']);

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $label = now()->subDays($i)->format('M d, Y');
                $dayMovements = $movements->filter(fn($m) => $m->created_at->format('Y-m-d') === $date);
                fputcsv($file, [
                    $label,
                    $dayMovements->where('type', 'in')->sum('quantity'),
                    $dayMovements->where('type', 'out')->sum('quantity'),
                ]);
            }

            fclose($file);
        };

        ActivityService::log('inventory_exported', 'exported inventory report (CSV)', 'Inventory', 'info');

        return response()->stream($callback, 200, $headers);
    }

    public function exportMovements(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(14);
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();

        $movements = StockMovement::with(['product.category', 'user'])
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->whereHas('product', fn($p) => $p->where('name', 'like', '%' . $request->search . '%')))
            ->latest()
            ->get();

        $filename = 'stock_movements_' . $start->format('Ymd') . '_to_' . $end->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($movements, $start, $end) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['STOCK MOVEMENTS REPORT']);
            fputcsv($file, ['Period: ' . $start->format('M d, Y') . ' - ' . $end->format('M d, Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Product', 'Category', 'Type', 'Quantity', 'Reason', 'User']);

            foreach ($movements as $m) {
                fputcsv($file, [
                    $m->created_at->format('Y-m-d H:i'),
                    $m->product->name ?? '-',
                    $m->product->category->name ?? '-',
                    strtoupper($m->type),
                    $m->quantity,
                    $m->reason,
                    $m->user->name ?? '-',
                ]);
            }

            fclose($file);
        };

        ActivityService::log(
            'movements_exported',
            "exported stock movements report ({$start->format('M d')} - {$end->format('M d')})",
            'Inventory',
            'info'
        );

        return response()->stream($callback, 200, $headers);
    }
}
