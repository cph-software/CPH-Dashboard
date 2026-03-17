<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyToLocationsAndSegmentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('tyre_locations', 'tyre_company_id')) {
            Schema::table('tyre_locations', function (Blueprint $table) {
                $table->unsignedBigInteger('tyre_company_id')->nullable()->after('id');
                $table->foreign('tyre_company_id')->references('id')->on('tyre_companies')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('tyre_segments', 'tyre_company_id')) {
            Schema::table('tyre_segments', function (Blueprint $table) {
                $table->unsignedBigInteger('tyre_company_id')->nullable()->after('id');
                $table->foreign('tyre_company_id')->references('id')->on('tyre_companies')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_locations', function (Blueprint $table) {
            $table->dropForeign(['tyre_company_id']);
            $table->dropColumn('tyre_company_id');
        });

        Schema::table('tyre_segments', function (Blueprint $table) {
            $table->dropForeign(['tyre_company_id']);
            $table->dropColumn('tyre_company_id');
        });
    }
}
