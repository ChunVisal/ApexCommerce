<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'type', 'quantity', 'balance', 'reason', 'reference', 'notes', 'user_id', 'seen_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
