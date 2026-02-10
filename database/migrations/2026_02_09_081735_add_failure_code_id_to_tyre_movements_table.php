<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFailureCodeIdToTyreMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('failure_code_id')->nullable()->after('position_id');
            $table->string('target_status')->nullable()->after('movement_type');
            
            $table->foreign('failure_code_id')->references('id')->on('tyre_failure_codes')->onDelete('set null');
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
            $table->dropForeign(['failure_code_id']);
            $table->dropColumn(['failure_code_id', 'target_status']);
        });
    }
}
