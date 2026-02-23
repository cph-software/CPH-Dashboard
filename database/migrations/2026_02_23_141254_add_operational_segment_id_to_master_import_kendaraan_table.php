<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationalSegmentIdToMasterImportKendaraanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->unsignedBigInteger('operational_segment_id')->nullable()->after('area');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            $table->dropColumn('operational_segment_id');
        });
    }
}
