<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRtd4ToTyreMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_movements', 'rtd_1')) {
                $table->decimal('rtd_1', 8, 2)->nullable()->after('psi_reading');
                $table->decimal('rtd_2', 8, 2)->nullable()->after('rtd_1');
                $table->decimal('rtd_3', 8, 2)->nullable()->after('rtd_2');
                $table->decimal('rtd_4', 8, 2)->nullable()->after('rtd_3');
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
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->dropColumn(['rtd_1', 'rtd_2', 'rtd_3', 'rtd_4']);
        });
    }
}
