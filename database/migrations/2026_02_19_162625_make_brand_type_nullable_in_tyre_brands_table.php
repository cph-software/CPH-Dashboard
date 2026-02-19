<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeBrandTypeNullableInTyreBrandsTable extends Migration
{
    public function up()
    {
        Schema::table('tyre_brands', function (Blueprint $table) {
            $table->string('brand_type')->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('tyre_brands', function (Blueprint $table) {
            $table->string('brand_type')->nullable(false)->change();
        });
    }
}
