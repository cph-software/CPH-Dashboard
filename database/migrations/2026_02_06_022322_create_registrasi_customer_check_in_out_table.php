<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrasiCustomerCheckInOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registrasi_customer_check_in_out', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_checkin');
            $table->unsignedBigInteger('customer_id')->index('registrasi_customer_check_in_out_customer_id_foreign');
            $table->dateTime('check_in');
            $table->dateTime('check_out');
            $table->string('checkin_by');
            $table->string('checkout_by');
            $table->string('cabang');
            $table->longText('pengerjaan')->nullable();
            $table->string('status')->nullable();
            $table->string('alasan_batal')->nullable();
            $table->string('status_menginap')->nullable();
            $table->string('alasan_menginap')->nullable();
            $table->string('keterangan_menginap')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->string('bayar_dp')->nullable();
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
        Schema::dropIfExists('registrasi_customer_check_in_out');
    }
}
