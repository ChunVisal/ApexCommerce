<?php

namespace App\Services\Cashier;

use App\Models\Product;
use App\Models\CashierStock;
use App\Models\Categories;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    public static function getSummaryCards()
    {
        $cashierId = Auth::id();

        $stocks = CashierStock::where('cashier_id', $cashierId)
            ->whereHas('product', fn($q) => $q->where('has_uom', false)->orWhereNull('has_uom'))
            ->whereRaw('allocated_quantity > sold_quantity')
            ->get();

        $totalAllocated = CashierStock::where('cashier_id', $cashierId)
            ->whereHas('product', fn($q) => $q->where('has_uom', false)->orWhereNull('has_uom'))
            ->sum('allocated_quantity');

        $totalSold = CashierStock::where('cashier_id', $cashierId)
            ->whereHas('product', fn($q) => $q->where('has_uom', false)->orWhereNull('has_uom'))
            ->sum('sold_quantity');

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

    public static function getCashierProducts($cashierId)
    {
        return Product::with(['category', 'uoms', 'cashierStocks' => function ($q) use ($cashierId) {
            // find products belong by cashier id
            $q->where('cashier_id', $cashierId);
        }])
            // Filters WHICH products appear at all
            ->whereHas('cashierStocks', function ($q) use ($cashierId) {
                $q->where('cashier_id', $cashierId);
            })
            ->get()
            ->map(function ($product) {
                $product->allocated = $product->cashierStocks->sum('allocated_quantity');
                $product->sold = $product->cashierStocks->sum('sold_quantity');
                $product->remaining = $product->allocated - $product->sold - $product->lost;
                $product->lost = $product->cashierStocks->sum('lost_quantity');
                $product->revenue = $product->sold * $product->selling_price;
                $product->last_drop = $product->cashierStocks->max('created_at');
                $product->category_name = $product->category->name ?? '-';

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
            })
            // Option A: keep inactive products visible if cashier still has remaining stock to sell through
            ->filter(fn($product) => $product->status === 'active' || $product->remaining > 0)
            ->values();
    }

    public static function getCashierCategories($cashierId)
    {
        // display categories on dropdown only if at least one product in stock
        $categories = Categories::whereHas('products', function ($q) use ($cashierId) {
            $q->whereHas('cashierStocks', fn($sq) => $sq->where('cashier_id', $cashierId));
        })->get();

        foreach ($categories as $category) {
            // show each total products qty of categories
            $category->cashier_remaining = CashierStock::where('cashier_id', $cashierId)
                ->whereHas('product', fn($q) => $q->where('category_id', $category->id))
                ->sum('allocated_quantity');
        }

        return $categories;
    }
}
