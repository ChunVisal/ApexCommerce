<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class InventoryService
{
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
}
