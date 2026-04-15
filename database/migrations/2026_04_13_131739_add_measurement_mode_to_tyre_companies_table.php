<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeasurementModeToTyreCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_companies', 'measurement_mode')) {
                $table->enum('measurement_mode', ['KM', 'HM', 'BOTH'])->default('BOTH')->after('company_name');
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
        Schema::table('tyre_companies', function (Blueprint $table) {
            if (Schema::hasColumn('tyre_companies', 'measurement_mode')) {
                $table->dropColumn('measurement_mode');
            }
        });
    }
}
