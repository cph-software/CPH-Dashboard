<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePositionToStringInMonitoringTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->string('position')->change();
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->string('position')->change();
        });

        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            $table->string('position')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->integer('position')->change();
        });

        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->integer('position')->change();
        });

        Schema::table('tyre_monitoring_removal', function (Blueprint $table) {
            $table->integer('position')->change();
        });
    }
}
