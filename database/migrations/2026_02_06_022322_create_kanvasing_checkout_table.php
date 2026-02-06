<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanvasingCheckoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanvasing_checkout', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('product_id');
            $table->integer('qty');
            $table->unsignedBigInteger('order_id')->index('kanvasing_checkout_order_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanvasing_checkout');
    }
}
