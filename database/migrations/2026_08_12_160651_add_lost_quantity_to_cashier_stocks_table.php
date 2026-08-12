<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashier_stocks', function (Blueprint $table) {
            $table->unsignedInteger('lost_quantity')->default(0)->after('sold_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('cashier_stocks', function (Blueprint $table) {
            $table->dropColumn('lost_quantity');
        });
    }
};
