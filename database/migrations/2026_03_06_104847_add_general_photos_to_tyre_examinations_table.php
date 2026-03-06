<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeneralPhotosToTyreExaminationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_examinations', function (Blueprint $table) {
            $table->string('photo_unit_front')->nullable()->after('notes');
            $table->string('photo_unit_back')->nullable()->after('photo_unit_front');
            $table->string('photo_unit_odometer')->nullable()->after('photo_unit_back');
        });
    }

    public function down()
    {
        Schema::table('tyre_examinations', function (Blueprint $table) {
            $table->dropColumn(['photo_unit_front', 'photo_unit_back', 'photo_unit_odometer']);
        });
    }
}
