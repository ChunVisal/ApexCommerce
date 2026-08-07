<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categories extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'code',
        'svg',
        'name',
        'sort_order',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
