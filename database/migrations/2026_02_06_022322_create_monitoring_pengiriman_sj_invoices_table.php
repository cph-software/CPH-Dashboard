<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringPengirimanSjInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_pengiriman_sj_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_surat_jalan')->index('monitoring_pengiriman_sj_invoices_id_surat_jalan_foreign');
            $table->string('invoice_number')->index();
            $table->enum('status', ['0', '1', '2', '3', '4']);
            $table->enum('checker', ['0', '1']);
            $table->string('checker_by')->nullable();
            $table->dateTime('checker_at')->nullable();
            $table->dateTime('tgl_terima')->nullable();
            $table->dateTime('tgl_kembali')->nullable();
            $table->dateTime('tgl_terima_faktur')->nullable();
            $table->dateTime('tgl_terima_toko')->nullable();
            $table->string('toko')->nullable();
            $table->string('alamat')->nullable();
            $table->text('keterangan');
            $table->enum('ekspedisi', ['1'])->nullable();
            $table->enum('notif', ['0', '1', '2'])->nullable();
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
        Schema::dropIfExists('monitoring_pengiriman_sj_invoices');
    }
}
