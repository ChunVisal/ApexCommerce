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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('cashier_id')->nullable()->constrained('users');
            $table->foreignId('customer_id')->nullable()->constrained('customers'); // fixed table
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('net', 10, 2)->default(0);              
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('vip_discount', 10, 2)->default(0);      
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->string('refund_reason')->nullable();             
            $table->dateTime('refunded_at')->nullable();              
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
