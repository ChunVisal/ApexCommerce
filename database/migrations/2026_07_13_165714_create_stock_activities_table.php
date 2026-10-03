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
        if (Schema::hasTable('stock_activities')) {
            return;
        }

        Schema::create('stock_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->string('product_name')->nullable(); // request new products
            $table->integer('quantity_requested');
            $table->integer('quantity_approved')->nullable();
            $table->string('status')->default('pending');
            // pending, approved, on_hold, rejected, in_transit, received, disputed
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('dispute_reason')->nullable();
            $table->string('eta')->nullable();
            $table->text('cashier_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_activities');
    }
};
