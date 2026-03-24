<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RestoreLifetimeColumnsToTyres extends Migration
{
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            if (!Schema::hasColumn('tyres', 'total_lifetime_km')) {
                $table->decimal('total_lifetime_km', 24, 2)->default(0)->after('current_tread_depth');
            }
            if (!Schema::hasColumn('tyres', 'total_lifetime_hm')) {
                $table->decimal('total_lifetime_hm', 24, 2)->default(0)->after('total_lifetime_km');
            }
            if (!Schema::hasColumn('tyres', 'current_km')) {
                $table->decimal('current_km', 24, 2)->default(0)->after('total_lifetime_hm');
            }
            if (!Schema::hasColumn('tyres', 'current_hm')) {
                $table->decimal('current_hm', 24, 2)->default(0)->after('current_km');
            }
        });
    }

    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn(['total_lifetime_km', 'total_lifetime_hm', 'current_km', 'current_hm']);
        });
    }
}
