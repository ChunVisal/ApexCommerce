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
        Schema::table('product_uoms', function (Blueprint $table) {
            $table->string('name')->nullable()->after('uom_id');
            $table->string('code')->nullable()->after('name');
            $table->dropForeign(['uom_id']);
            $table->dropColumn('uom_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_uoms', function (Blueprint $table) {
            //
        });
    }
};
