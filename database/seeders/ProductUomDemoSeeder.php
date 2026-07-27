<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Uom;
use Illuminate\Database\Seeder;

class ProductUomDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create UOMs if not exist
        $pcs = Uom::firstOrCreate(['code' => 'PCS'], ['name' => 'Piece']);
        $box = Uom::firstOrCreate(['code' => 'BOX'], ['name' => 'Box']);
        $gram = Uom::firstOrCreate(['code' => 'G'], ['name' => 'Gram']);
        $meter = Uom::firstOrCreate(['code' => 'M'], ['name' => 'Meter']);

        // Product 1: Thermal Paste
        $thermal = Product::firstOrCreate(
            ['code' => 'PROD-THP-0001'],
            [
                'name' => 'Thermal Paste Arctic MX-6',
                'category_id' => 7,
                'has_uom' => true,
                'cost_price' => 3.00,
                'selling_price' => 5.00,
                'stock_quantity' => 300,
                'low_stock_threshold' => 10,
                'status' => 'active',
                'barcode' => 'THP001',
            ]
        );

        $thermal->uoms()->sync([
            $gram->id => [
                'quantity_per_unit' => 1,
                'price' => 5.00,
                'is_default' => true,
            ],
            $box->id => [
                'quantity_per_unit' => 30,
                'price' => 45.00,
                'is_default' => false,
            ],
        ]);

        // Product 2: Ethernet Cable
        $cable = Product::firstOrCreate(
            ['code' => 'PROD-ETH-0001'],
            [
                'name' => 'Ethernet Cable Cat6',
                'category_id' => 7,
                'has_uom' => true,
                'cost_price' => 3.00,
                'selling_price' => 2.00,
                'stock_quantity' => 500,
                'low_stock_threshold' => 20,
                'status' => 'active',
                'barcode' => 'ETH001',
            ]
        );

        $cable->uoms()->sync([
            $meter->id => [
                'quantity_per_unit' => 1,
                'price' => 2.00,
                'is_default' => true,
            ],
            $box->id => [
                'quantity_per_unit' => 100,
                'price' => 150.00,
                'is_default' => false,
            ],
        ]);

        // Product 3: M3 Screws
        $screws = Product::firstOrCreate(
            ['code' => 'PROD-SCR-0001'],
            [
                'name' => 'M3 Screws Pack',
                'category_id' => 7,
                'has_uom' => true,
                'cost_price' => 3.00,
                'selling_price' => 0.10,
                'stock_quantity' => 5000,
                'low_stock_threshold' => 100,
                'status' => 'active',
                'barcode' => 'SCR001',
            ]
        );

        $screws->uoms()->sync([
            $pcs->id => [
                'quantity_per_unit' => 1,
                'price' => 0.10,
                'is_default' => true,
            ],
            $box->id => [
                'quantity_per_unit' => 200,
                'price' => 15.00,
                'is_default' => false,
            ],
        ]);

        echo "3 demo products with UOMs created!\n";
    }
}
