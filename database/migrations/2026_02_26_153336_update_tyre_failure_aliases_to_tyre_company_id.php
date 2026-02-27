<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTyreFailureAliasesToTyreCompanyId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_failure_aliases', function (Blueprint $table) {
            $table->unsignedBigInteger('tyre_company_id')->after('id');
            $table->foreign('tyre_company_id')->references('id')->on('tyre_companies')->onDelete('cascade');
            $table->dropColumn('toko_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_failure_aliases', function (Blueprint $table) {
            $table->dropForeign(['tyre_company_id']);
            $table->dropColumn('tyre_company_id');
            $table->unsignedBigInteger('toko_id')->after('id');
        });
    }
}
