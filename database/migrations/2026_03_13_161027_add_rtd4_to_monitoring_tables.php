<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRtd4ToMonitoringTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->decimal('rtd_4', 5, 2)->nullable()->after('rtd_3');
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->decimal('rtd_4', 5, 2)->nullable()->after('rtd_3');
        });
    }

    public function down()
    {
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->dropColumn('rtd_4');
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->dropColumn('rtd_4');
        });
    }
}
