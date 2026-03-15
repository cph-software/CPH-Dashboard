<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesToTyreMonitoringImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_monitoring_images', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_images', 'notes')) {
                $table->text('notes')->nullable()->after('uploaded_by');
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
        Schema::table('tyre_monitoring_images', function (Blueprint $table) {
            if (Schema::hasColumn('tyre_monitoring_images', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
}
