<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSerahTerimaKreditItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serah_terima_kredit_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('serah_terima_id')->index('serah_terima_kredit_items_serah_terima_id_foreign');
            $table->string('jenis_barang_servis');
            $table->double('qty');
            $table->string('satuan');
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('serah_terima_kredit_items');
    }
}
