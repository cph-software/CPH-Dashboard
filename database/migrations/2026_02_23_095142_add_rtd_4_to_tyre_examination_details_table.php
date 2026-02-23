<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRtd4ToTyreExaminationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_examination_details', function (Blueprint $table) {
            $table->decimal('rtd_4', 5, 2)->nullable()->after('rtd_3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_examination_details', function (Blueprint $table) {
            $table->dropColumn('rtd_4');
        });
    }
}
