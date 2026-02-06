<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdAndOrderToMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('aplikasi_id');
            $table->integer('order_no')->default(0)->after('icon');
            $table->boolean('is_active')->default(true)->after('order_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'order_no', 'is_active']);
        });
    }
}
