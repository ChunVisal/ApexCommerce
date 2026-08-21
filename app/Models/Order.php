<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['order_number', 'cashier_id', 'customer_id', 'subtotal', 'tax', 'total', 'net', 'discount', 'vip_discount', 'status', 'notes', 'refund_reason', 'refunded_at'];
    
    protected $casts = [
        'refunded_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
