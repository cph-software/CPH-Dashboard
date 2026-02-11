<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceMetricsToTyresTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->nullable()->after('tyre_size_id');
            $table->decimal('initial_tread_depth', 5, 2)->nullable()->after('price'); // OTD
            $table->decimal('current_tread_depth', 5, 2)->nullable()->after('initial_tread_depth'); // RTD
            $table->integer('retread_count')->default(0)->after('status'); // 0=New, 1=R1, etc.
        });

        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->decimal('rtd_reading', 5, 2)->nullable()->after('psi_reading'); // Reading at movement time
        });
    }

    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn(['price', 'initial_tread_depth', 'current_tread_depth', 'retread_count']);
        });

        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->dropColumn('rtd_reading');
        });
    }
}
