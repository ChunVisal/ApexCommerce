<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['in', 'out']);          // money in / money out
            $table->decimal('net_amount', 12, 2)->nullable(); // net amount after tax and discounts
            $table->decimal('amount', 12, 2);
            $table->string('category', 255);          // sale, purchase, refund, expense
            $table->string('source', 255)->nullable();  // where the money came from or went to
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('seen_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_movements');
    }
};
