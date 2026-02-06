<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVulkanisirCanceledTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vulkanisir_canceled', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transid');
            $table->longText('alasan');
            $table->string('canceled_by');
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
        Schema::dropIfExists('vulkanisir_canceled');
    }
}
