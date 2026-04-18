<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxUsersToTyreCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_companies', function (Blueprint $table) {
            $table->unsignedInteger('max_users')->default(10)->after('total_tyre_capacity')->comment('Max users per company allowed');
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
            $table->dropColumn('max_users');
        });
    }
}
