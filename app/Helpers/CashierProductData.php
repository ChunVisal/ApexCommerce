<?php

namespace App\Helpers;

use App\Models\CashierStock;
use Illuminate\Support\Facades\Auth;

class CashierProductData
{
    public static function getSummaryCards()
    {
        $cashierId = Auth::id();

        $stocks = CashierStock::where('cashier_id', $cashierId)
            ->whereRaw('allocated_quantity > sold_quantity')
            ->get();

        $totalAllocated = CashierStock::where('cashier_id', $cashierId)->sum('allocated_quantity');
        $totalSold = CashierStock::where('cashier_id', $cashierId)->sum('sold_quantity');
        $totalRemaining = $totalAllocated - $totalSold;
        $totalProducts = CashierStock::where('cashier_id', $cashierId)
            ->distinct('product_id')
            ->count('product_id');

        $totalValue = CashierStock::where('cashier_id', $cashierId)
            ->join('products', 'cashier_stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM((allocated_quantity - sold_quantity) * selling_price) as total_value')
            ->value('total_value') ?? 0;

        $lowStock = CashierStock::where('cashier_id', $cashierId)
            ->selectRaw('product_id, SUM(allocated_quantity) as total_allocated, SUM(sold_quantity) as total_sold')
            ->groupBy('product_id')
            ->havingRaw('(total_allocated - total_sold) > 0')
            ->havingRaw('(total_allocated - total_sold) <= 5')
            ->count();

        $outOfStock = CashierStock::where('cashier_id', $cashierId)
            ->whereRaw('allocated_quantity <= sold_quantity')
            ->distinct('product_id')
            ->count('product_id');

        return [
            [
                'title' => 'Total Products',
                'value' => $totalProducts,
                'icon' => 'fa-solid fa-cube',
                'iconBg' => '#0F6E8C',
                'iconColor' => '#0F6E8C',
                'dot' => '#0F6E8C',
                'subtitle' => '$' . number_format($totalValue) . ' total value',
            ],
            [
                'title' => 'Allocated',
                'value' => $totalAllocated,
                'icon' => 'fa-solid fa-truck-loading',
                'iconBg' => '#8B5CF6',
                'iconColor' => '#8B5CF6',
                'dot' => '#8B5CF6',
                'subtitle' => 'Total received',
            ],
            [
                'title' => 'Remaining',
                'value' => $totalRemaining,
                'icon' => 'fa-solid fa-boxes-stacked',
                'iconBg' => '#10B981',
                'iconColor' => '#10B981',
                'dot' => '#10B981',
                'subtitle' => $totalSold . ' sold',
            ],
            [
                'title' => 'Low Stock',
                'value' => $lowStock,
                'icon' => 'fa-solid fa-triangle-exclamation',
                'iconBg' => $lowStock > 0 ? '#EF4444' : '#F59E0B',
                'iconColor' => $lowStock > 0 ? '#EF4444' : '#F59E0B',
                'dot' => $lowStock > 0 ? '#EF4444' : '#F59E0B',
                'subtitle' => $outOfStock . ' out of stock',
            ],
        ];
    }
}
