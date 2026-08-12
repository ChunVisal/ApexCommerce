<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashierStock extends Model
{
    protected $fillable = ['product_id', 'cashier_id', 'allocated_quantity', 'sold_quantity', 'lost_quantity', 'allocated_by'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }
}
