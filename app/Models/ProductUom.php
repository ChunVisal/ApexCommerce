<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUom extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'quantity_per_unit',
        'price',
        'description',
        'is_default',
        'has_uom',
    ];

    protected $casts = [
        'has_uom' => 'boolean',
        'is_default' => 'boolean',
    ];
}
