<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranInvoiceArAgingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembayaran_invoice_ar_aging', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('workplace');
            $table->string('invoice');
            $table->date('tgl_invoice');
            $table->date('jth_tempo');
            $table->string('kode_sales');
            $table->string('nama_sales');
            $table->string('kode_pel');
            $table->string('nama_pel');
            $table->string('aging');
            $table->string('rp_due_in_week');
            $table->string('rp_current_due');
            $table->string('sisa_piutang');
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
        Schema::dropIfExists('pembayaran_invoice_ar_aging');
    }
}
