<?php

namespace Database\Seeders;

use App\Models\ProductCatalog;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        ProductCatalog::truncate();
    }
}
