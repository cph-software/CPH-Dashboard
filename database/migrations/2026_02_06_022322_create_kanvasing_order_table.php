<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanvasingOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanvasing_order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('order_code')->unique();
            $table->string('status');
            $table->string('nomor_kv')->nullable();
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
        Schema::dropIfExists('kanvasing_order');
    }
}
