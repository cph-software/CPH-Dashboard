<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastInspectionDateToTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            if (!Schema::hasColumn('tyres', 'last_inspection_date')) {
                $table->date('last_inspection_date')->nullable()->after('current_tread_depth');
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
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn('last_inspection_date');
        });
    }
}
