<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('stock_requests', 'stock_activities');
    }

    public function down(): void
    {
        Schema::rename('stock_activities', 'stock_requests');
    }
};