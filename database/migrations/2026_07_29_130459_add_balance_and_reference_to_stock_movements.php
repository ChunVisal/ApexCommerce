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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('balance')->nullable()->after('quantity');
            $table->string('reference')->nullable()->after('reason');
        });
    }

    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['balance', 'reference']);
        });
    }
};
