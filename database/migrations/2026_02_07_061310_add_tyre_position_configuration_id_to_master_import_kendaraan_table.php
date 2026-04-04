<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTyrePositionConfigurationIdToMasterImportKendaraanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_import_kendaraan', function (Blueprint $table) {
            if (!Schema::hasColumn('master_import_kendaraan', 'tyre_position_configuration_id')) {
                $table->unsignedBigInteger('tyre_position_configuration_id')->nullable()->after('total_tyre_position');
                $table->foreign('tyre_position_configuration_id', 'mik_tyre_pos_config_foreign')
                      ->references('id')->on('tyre_position_configurations')
                      ->onDelete('set null');
            }
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
            $table->dropForeign('mik_tyre_pos_config_foreign');
            $table->dropColumn('tyre_position_configuration_id');
        });
    }
}
