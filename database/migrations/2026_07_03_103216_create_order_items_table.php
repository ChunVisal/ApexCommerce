<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('name');
            $table->string('base_unit')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->unsignedInteger('refunded_quantity')->default(0);
            $table->decimal('total', 10, 2);
            $table->boolean('is_refunded')->default(false);
            $table->string('refund_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
