<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Uom;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'barcode',
        'selling_price',
        'stock_quantity',
        'status',
        'cost_price',
        'brand',
        'image',
        'low_stock_threshold',
        'has_uom',
        'base_unit_name',
        'base_unit_code',
    ];

    public function uoms()
    {
        return $this->hasMany(ProductUom::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    public function cashierStocks()
    {
        return $this->hasMany(CashierStock::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
