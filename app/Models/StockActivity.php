<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockActivity extends Model
{
    protected $fillable = [
        'cashier_id',
        'product_id',
        'product_name',
        'quantity_requested',
        'quantity_approved',
        'status',
        'approved_by',
        'dispute_reason',
        'eta',
        'confirmed_at',
        'cashier_notes',
        'seen_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
