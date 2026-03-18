<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotosToOperationsTables extends Migration
{
    public function up()
    {
        // 1. Tyre Movements (Installation, Removal, Rotation)
        Schema::table('tyre_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_movements', 'photo')) {
                $table->string('photo')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('tyre_movements', 'photo_target')) {
                $table->string('photo_target')->nullable()->after('photo');
            }
        });

        // 2. Tyre Examination (Standardize column name if needed, but details already has 'photo')
        // We might want to add more photo slots to details if they want multiple photos like monitoring
        Schema::table('tyre_examination_details', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_examination_details', 'photo_serial')) {
                $table->string('photo_serial')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('tyre_examination_details', 'photo_rtd')) {
                $table->string('photo_rtd')->nullable()->after('photo_serial');
            }
        });
    }

    public function down()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->dropColumn(['photo', 'photo_target']);
        });
        Schema::table('tyre_examination_details', function (Blueprint $table) {
            $table->dropColumn(['photo_serial', 'photo_rtd']);
        });
    }
}
