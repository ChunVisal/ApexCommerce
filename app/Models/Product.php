<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    // Add for explicitly, doesn't matter if delete
    protected $table = 'products';

    // tells Laravel which columns in table DB assigned 
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

    public function category()
    {
        return $this->belongsTo(Categories::class);
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
