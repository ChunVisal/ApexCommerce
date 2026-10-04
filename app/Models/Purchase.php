<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FinancialMovement;

// app/Models/Purchase.php
class Purchase extends Model
{
    protected $fillable = ['purchase_number', 'vendor_name', 'total', 'notes', 'user_id'];
    
    public function financialMovements()
    {
        return $this->morphMany(FinancialMovement::class, 'source');
    }
}
