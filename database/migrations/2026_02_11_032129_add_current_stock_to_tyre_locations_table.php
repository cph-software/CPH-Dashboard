<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentStockToTyreLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_locations', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('capacity');
        });
    }

    public function down()
    {
        Schema::table('tyre_locations', function (Blueprint $table) {
            $table->dropColumn('current_stock');
        });
    }
}
