<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentCompanyIdToTyreCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_companies', 'parent_company_id')) {
                $table->unsignedBigInteger('parent_company_id')->nullable()->after('id');
                $table->foreign('parent_company_id')->references('id')->on('tyre_companies')->onDelete('set null');
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
            if (Schema::hasColumn('tyre_companies', 'parent_company_id')) {
                $table->dropForeign(['parent_company_id']);
                $table->dropColumn('parent_company_id');
            }
        });
    }
}
