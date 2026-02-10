<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToTyreMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('operational_segment_id')->nullable()->after('position_id');
            $table->string('work_location')->nullable()->after('operational_segment_id');
            $table->time('start_time')->nullable()->after('work_location');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('tyreman_1')->nullable()->after('end_time');
            $table->string('tyreman_2')->nullable()->after('tyreman_1');
            $table->decimal('psi_reading', 10, 2)->nullable()->after('tyreman_2');
            $table->boolean('new_bolts_used')->default(false)->after('psi_reading');
            $table->string('rim_size')->nullable()->after('new_bolts_used');

            $table->foreign('operational_segment_id')->references('id')->on('tyre_segments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_movements', function (Blueprint $table) {
            $table->dropForeign(['operational_segment_id']);
            $table->dropColumn([
                'operational_segment_id',
                'work_location',
                'start_time',
                'end_time',
                'tyreman_1',
                'tyreman_2',
                'psi_reading',
                'new_bolts_used',
                'rim_size'
            ]);
        });
    }
}
