<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayNameToTyreFailureCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_failure_codes', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('failure_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_failure_codes', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
}
