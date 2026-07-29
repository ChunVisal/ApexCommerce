<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class InventoryData
{
    public static function getStockItems()
    {
        // Replace ProductData::getProducts() with direct Product model query to avoid undefined method
        $products = Product::query()
            ->get()
            ->map(function ($p) {
                return [
                    'code' => $p->code ?? $p->product_code ?? null,
                    'stock' => $p->stock_quantity ?? $p->stock ?? 0,
                    'price' => $p->selling_price ?? $p->price ?? $p->cost_price ?? 0,
                    // preserve other attributes if present
                    'name' => $p->name ?? null,
                ];
            })
            ->toArray();

        $reorderLevels = [
            'PRD-0001' => 10,
            'PRD-0002' => 10,
            'PRD-0003' => 10,
            'PRD-0004' => 10,
            'PRD-0005' => 15,
            'PRD-0006' => 15,
            'PRD-0007' => 10,
            'PRD-0008' => 10,
            'PRD-0009' => 8,
            'PRD-0010' => 8,
            'PRD-0011' => 10,
            'PRD-0012' => 8,
            'PRD-0013' => 8,
            'PRD-0014' => 8,
            'PRD-0015' => 15,
        ];

        $lastUpdated = [
            'PRD-0001' => '2024-11-22',
            'PRD-0002' => '2024-11-21',
            'PRD-0003' => '2024-11-24',
            'PRD-0004' => '2024-11-20',
            'PRD-0005' => '2024-11-23',
            'PRD-0006' => '2024-11-19',
            'PRD-0007' => '2024-11-24',
            'PRD-0008' => '2024-11-18',
            'PRD-0009' => '2024-11-22',
            'PRD-0010' => '2024-11-23',
            'PRD-0011' => '2024-11-21',
            'PRD-0012' => '2024-11-20',
            'PRD-0013' => '2024-11-17',
            'PRD-0014' => '2024-11-23',
            'PRD-0015' => '2024-11-25',
        ];

        return array_map(function ($product) use ($reorderLevels, $lastUpdated) {
            $product['reorder_level'] = $reorderLevels[$product['code']] ?? 10;
            $product['last_updated'] = $lastUpdated[$product['code']] ?? '2024-11-20';

            return $product;
        }, $products);
    }

    public static function getSummaryCards()
    {
        $products = Product::where('has_uom', false)->get();

        $todayNewStockIn = StockMovement::whereDate('created_at', Carbon::today())
            ->where('type', 'in')
            ->sum('quantity');

        $todayStockOut = StockMovement::whereDate('created_at', Carbon::today())
            ->where('type', 'out')
            ->sum('quantity');

        $totalStock = $products->sum('stock_quantity');
        $totalProducts = Product::count();
        $stockValue = (float) (Product::selectRaw('SUM(stock_quantity * selling_price) as total')->value('total') ?? 0);
        $lowStock = Product::whereColumn('stock_quantity', '<', 'low_stock_threshold')->where('stock_quantity', '>', 0)->count();
        $outOfStock = Product::where('stock_quantity', '<=', 0)->count();
        $lowStockPercent = $totalProducts > 0 ? round(($lowStock / $totalProducts) * 100, 1) : 0;
        $outOfStockPercent = $totalProducts > 0 ? round(($outOfStock / $totalProducts) * 100, 1) : 0;

        return [
            [
                'title' => 'Total Products',
                'value' => $totalProducts,
                'icon' => 'fa-solid fa-cube',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'trend' => $todayNewStockIn > 0 ? 'up' : 'down',
                'percentage' => '+' . $todayNewStockIn . ' today',
                'period' => $totalStock . ' in stock',
            ],
            [
                'title' => 'Low Stock',
                'value' => $lowStock,
                'icon' => 'fa-solid fa-triangle-exclamation',
                'iconBg' => '#F59E0B',
                'iconColor' => '#D97706',
                'trend' => $todayStockOut > 0 ? 'up' : 'down',
                'percentage' => '-' . $todayStockOut . ' today',
                'period' => $lowStockPercent . '% of products',
            ],
            [
                'title' => 'Out of Stock',
                'value' => $outOfStock,
                'icon' => 'fa-solid fa-circle-xmark',
                'iconBg' => '#EF4444',
                'iconColor' => '#EF4444',
                'trend' => $outOfStock <= 0 ? 'up' : 'down',
                'percentage' => $outOfStockPercent . '%',
                'period' => 'of products',
            ],
            [
                'title' => 'Stock Value',
                'value' => '$' . number_format($stockValue, 0),
                'icon' => 'fa-solid fa-sack-dollar',
                'iconBg' => '#10B981',
                'iconColor' => '#10B981',
                'trend' => $todayNewStockIn > $todayStockOut ? 'up' : 'down',
                'percentage' => $totalStock . ' units',
                'period' => 'Total value',
            ],
        ];
    }

    public static function getSummary()
    {
        $items = self::getStockItems();

        $totalProducts = count($items);
        $lowStock = collect($items)->filter(fn($i) => $i['stock'] > 0 && $i['stock'] < $i['reorder_level'])->count();
        $outOfStock = collect($items)->filter(fn($i) => $i['stock'] <= 0)->count();
        $totalValue = collect($items)->sum(fn($i) => $i['price'] * $i['stock']);

        return [
            'total_products' => $totalProducts,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_value' => $totalValue,
        ];
    }

    private function getMovementTrend()
    {
        $movements = StockMovement::orderBy('created_at', 'asc')->get();

        if ($movements->isEmpty()) {
            return [
                'labels' => [],
                'stock_in' => [],
                'stock_out' => [],
            ];
        }

        $grouped = $movements->groupBy(function ($item) {
            return $item->created_at->format('d M');
        });

        $labels = [];
        $stockIn = [];
        $stockOut = [];

        foreach ($grouped as $time => $items) {
            $labels[] = $time;
            $stockIn[] = $items->where('type', 'in')->sum('quantity');
            $stockOut[] = $items->where('type', 'out')->sum('quantity');
        }

        return [
            'labels' => $labels,
            'stock_in' => $stockIn,
            'stock_out' => $stockOut,
        ];
    }
}
