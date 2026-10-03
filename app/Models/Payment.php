<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FinancialMovement;

class Payment extends Model
{
    protected $fillable = ['order_id', 'method', 'amount', 'amount_received', 'change', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function financialMovements()
    {
        return $this->morphMany(FinancialMovement::class, 'source');
    }
}
