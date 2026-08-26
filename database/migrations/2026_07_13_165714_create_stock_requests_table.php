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
        Schema::create('stock_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity_requested');
            $table->integer('quantity_approved')->nullable();
            $table->string('status')->default('pending');
            // pending, approved, on_hold, rejected, in_transit, received, disputed
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('dispute_reason')->nullable();
            $table->string('eta')->nullable();
            $table->text('cashier_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('seen_at')->nullable()->default(null);
            $table->timestamps();

            // request new products
            $table->string('product_name')->nullable()->after('product_id');
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
