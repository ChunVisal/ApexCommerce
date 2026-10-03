<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\FinancialMovement;
use App\Models\Categories;
use App\Services\Admin\ActivityService;
use Illuminate\Support\Carbon;
use App\Models\User;

class MovementHistoryController extends Controller
{
    // items stock movement 
    public function movements(Request $request)
    {
        $start = $request->start_date
            ? Carbon::parse($request->start_date)
            : now()->subDays(14);
        $end = $request->end_date
            ? Carbon::parse($request->end_date)
            : now();

        $stockmovements = StockMovement::with(['product.category', 'user'])
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->latest()
            ->get();

        // mark all currently-unseen movements as seen
        StockMovement::whereNull('seen_at')->update(['seen_at' => now()]);

        if ($request->ajax()) {
            return response()->json([
                'stockmovements' => $stockmovements,
            ]);
        }

        $categories = Categories::orderBy('name')->get();
        $users = User::all();

        return view('admin.stock-movement.index', compact('stockmovements', 'start', 'end', 'categories', 'users'));
    }

    public function movementsCount()
    {
        return response()->json([
            'count' => StockMovement::whereNull('seen_at')->count(),
        ]);
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

    // value financal movements
    public function financialMovements(Request $request)
    {
        $start = $request->start_date
            ? Carbon::parse($request->start_date)
            : now()->subDays(14);
        $end = $request->end_date
            ? Carbon::parse($request->end_date)
            : now();

        $financialmovements = FinancialMovement::with(['user'])
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->latest()
            ->get();

        // mark all currently-unseen movements as seen
        FinancialMovement::whereNull('seen_at')->update(['seen_at' => now()]);

        if ($request->ajax()) {
            return response()->json([
                'financialmovements' => $financialmovements,
            ]);
        }

        $users = User::all();

        return view('admin.financial-movement.index', compact('financialmovements', 'start', 'end', 'users'));
    }

    public function financialMovementsCount()
    {
        return response()->json([
            'count' => FinancialMovement::whereNull('seen_at')->count(),
        ]);
    }

    public function exportFinancialMovements(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(14);
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();

        $movements = FinancialMovement::with(['user'])
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->where('reference', 'like', '%' . $request->search . '%'))
            ->latest()
            ->get();

        $filename = 'financial_movements_' . $start->format('Ymd') . '_to_' . $end->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($movements, $start, $end) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['FINANCIAL MOVEMENTS REPORT']);
            fputcsv($file, ['Period: ' . $start->format('M d, Y') . ' - ' . $end->format('M d, Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Type', 'Amount', 'Discount', 'Net Amount', 'Category', 'Source', 'Reference', 'Notes', 'User']);

            foreach ($movements as $m) {
                fputcsv($file, [
                    $m->created_at->format('Y-m-d H:i'),
                    strtoupper($m->type),
                    number_format($m->amount, 2),
                    number_format($m->discount ?? 0, 2),
                    number_format($m->net_amount ?? 0, 2),
                    strtoupper($m->category),
                    strtoupper($m->source_type ?? '-'),
                    strtoupper($m->reference ?? '-'),
                    strtoupper($m->notes ?? '-'),
                    strtoupper($m->user?->name ?? '-'),
                ]);
            }

            fclose($file);
        };

        ActivityService::log(
            'financial_movements_exported',
            "exported financial movements report ({$start->format('M d')} - {$end->format('M d')})",
            'Inventory',
            'info'
        );

        return response()->stream($callback, 200, $headers);
    }
}
