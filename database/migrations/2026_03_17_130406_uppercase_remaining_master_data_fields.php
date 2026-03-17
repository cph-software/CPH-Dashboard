<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UppercaseRemainingMasterDataFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE tyres SET serial_number = UPPER(serial_number)");
        DB::statement("UPDATE tyre_locations SET location_name = UPPER(location_name)");
        DB::statement("UPDATE tyre_segments SET segment_id = UPPER(segment_id), segment_name = UPPER(segment_name)");
        DB::statement("UPDATE master_import_kendaraan SET kode_kendaraan = UPPER(kode_kendaraan), no_polisi = UPPER(no_polisi)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
