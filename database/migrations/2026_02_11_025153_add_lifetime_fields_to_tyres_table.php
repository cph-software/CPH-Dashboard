<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLifetimeFieldsToTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->decimal('total_lifetime_km', 15, 2)->default(0)->after('status');
            $table->decimal('total_lifetime_hm', 15, 2)->default(0)->after('total_lifetime_km');
        });
    }

    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn(['total_lifetime_km', 'total_lifetime_hm']);
        });
    }
}
