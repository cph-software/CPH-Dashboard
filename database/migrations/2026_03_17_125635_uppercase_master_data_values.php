<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UppercaseMasterDataValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tyre_brands')->update([
            'brand_name' => \DB::raw('UPPER(brand_name)')
        ]);

        \DB::table('tyre_patterns')->update([
            'name' => \DB::raw('UPPER(name)')
        ]);

        \DB::table('tyre_sizes')->update([
            'size' => \DB::raw('UPPER(size)')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No easy way to revert to original case, usually uppercase is fine as a permanent state
    }
}
