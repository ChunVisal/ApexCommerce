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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_name');
            $table->string('action');           // e.g., 'sale_completed', 'stock_adjust', 'user_login'
            $table->string('description');      // e.g., 'Sold INV-00125 for $750.31'
            $table->string('page');             // e.g., 'POS', 'Inventory', 'Products', 'Users'
            $table->string('status')->default('info'); // info, success, warning, danger
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
