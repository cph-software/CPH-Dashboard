<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterImportKendaraanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_import_kendaraan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_kendaraan');
            $table->string('no_polisi');
            $table->string('jenis_kendaraan');
            $table->string('tipe_kendaraan')->nullable();
            $table->string('tahun_rakit')->nullable();
            $table->string('usia_kendaraan')->nullable();
            $table->string('kapasitas_silinder')->nullable();
            $table->string('no_bpkb')->nullable();
            $table->string('no_rangka')->nullable();
            $table->string('no_mesin')->nullable();
            $table->string('area');
            $table->timestamps();
            $table->integer('total_tyre_position')->default(0);
            $table->enum('tyre_unit_status', ['Active', 'Inactive', 'Maintenance'])->default('Active');
            $table->unsignedBigInteger('tyre_position_configuration_id')->nullable()->index('master_import_kendaraan_tyre_position_configuration_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_import_kendaraan');
    }
}
