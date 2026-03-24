<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalTyresToTyreCompanies extends Migration
{
    public function up()
    {
        Schema::table('tyre_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_companies', 'total_tyres')) {
                $table->integer('total_tyres')->default(0)->after('company_name');
            }
        });
    }

    public function down()
    {
        Schema::table('tyre_companies', function (Blueprint $table) {
            $table->dropColumn('total_tyres');
        });
    }
}
