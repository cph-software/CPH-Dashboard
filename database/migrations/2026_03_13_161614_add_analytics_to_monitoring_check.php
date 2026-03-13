<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnalyticsToMonitoringCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->decimal('worn_percentage', 8, 2)->nullable()->after('rtd_4');
            $table->decimal('km_per_mm', 12, 2)->nullable()->after('worn_percentage');
            $table->decimal('projected_life_km', 15, 2)->nullable()->after('km_per_mm');
            $table->date('date_assembly')->nullable()->after('inf_press_actual');
            $table->date('date_inspection')->nullable()->after('date_assembly');
        });

        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->date('date_assembly')->nullable()->after('inf_press_actual');
            $table->date('date_inspection')->nullable()->after('date_assembly');
        });
    }

    public function down()
    {
        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->dropColumn(['worn_percentage', 'km_per_mm', 'projected_life_km', 'date_assembly', 'date_inspection']);
        });

        Schema::table('tyre_monitoring_installation', function (Blueprint $table) {
            $table->dropColumn(['date_assembly', 'date_inspection']);
        });
    }
}
