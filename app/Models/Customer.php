<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Customer.php
class Customer extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'email', 'segment', 'total_orders', 'total_spent', 'last_order_at'];

    protected $casts = [
        'last_order_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
