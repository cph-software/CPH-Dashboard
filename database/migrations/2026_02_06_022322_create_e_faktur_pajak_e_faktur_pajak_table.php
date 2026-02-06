<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEFakturPajakEFakturPajakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('e_faktur_pajak_e_faktur_pajak', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->year('tahun');
            $table->string('bulan');
            $table->string('npwp_langganan');
            $table->string('tgl_npwp');
            $table->string('no_seri_npwp');
            $table->string('file_name');
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
        Schema::dropIfExists('e_faktur_pajak_e_faktur_pajak');
    }
}
