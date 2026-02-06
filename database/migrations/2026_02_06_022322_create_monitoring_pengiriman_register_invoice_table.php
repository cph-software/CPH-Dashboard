<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringPengirimanRegisterInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_pengiriman_register_invoice', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('workplace');
            $table->string('id_customer');
            $table->string('customer_name');
            $table->string('invoice_date');
            $table->string('fdjr');
            $table->string('invoice_number');
            $table->string('amount');
            $table->string('cancel');
            $table->string('register_invoice');
            $table->string('register_date');
            $table->integer('id_surat_jalan');
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
        Schema::dropIfExists('monitoring_pengiriman_register_invoice');
    }
}
