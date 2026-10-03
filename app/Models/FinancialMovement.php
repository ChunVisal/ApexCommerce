<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialMovement extends Model
{
    protected $table = 'financial_movements';

    protected $fillable = [
        'type',
        'discount',
        'net_amount',
        'amount',
        'category',
        'source',
        'reference',
        'notes',
        'user_id',
        'seen_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
