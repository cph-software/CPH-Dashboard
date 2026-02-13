<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstallConditionToTyreMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->enum('install_condition', ['New', 'Spare', 'Repair'])->nullable()->after('movement_type');
            $table->boolean('is_replacement')->default(false)->after('install_condition');
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
            $table->dropColumn(['install_condition', 'is_replacement']);
        });
    }
}
