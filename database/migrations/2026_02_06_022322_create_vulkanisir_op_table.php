<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVulkanisirOpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vulkanisir_op', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transid');
            $table->string('no_op');
            $table->date('tanggal');
            $table->string('no_faktur')->nullable();
            $table->string('no_terima_barang')->nullable();
            $table->string('no_transfer_stok')->nullable();
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
        Schema::dropIfExists('vulkanisir_op');
    }
}
