<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVulkanisirDetailOpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vulkanisir_detail_op', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transid');
            $table->string('no_op');
            $table->string('ukuran');
            $table->string('merk')->nullable();
            $table->string('ply')->nullable();
            $table->string('no_seri');
            $table->string('telapak')->nullable();
            $table->string('tgl_selesai')->nullable();
            $table->string('tolakan')->nullable();
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
        Schema::dropIfExists('vulkanisir_detail_op');
    }
}
