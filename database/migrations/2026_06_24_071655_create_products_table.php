<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('brand')->nullable();
            $table->string('barcode')->unique()->nullable();

            $table->foreignId('category_id')->constrained();

            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);

            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->boolean('has_uom')->default(false);
            $table->string('base_unit_name')->nullable();
            $table->string('base_unit_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
