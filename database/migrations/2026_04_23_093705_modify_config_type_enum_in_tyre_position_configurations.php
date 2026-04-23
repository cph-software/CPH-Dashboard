<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyConfigTypeEnumInTyrePositionConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ubah ENUM menjadi VARCHAR(50) agar bisa menerima kata "Standard" atau konfigurasi lainnya tanpa error.
        DB::statement("ALTER TABLE tyre_position_configurations MODIFY COLUMN config_type VARCHAR(50) DEFAULT 'Rigid'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_position_configurations', function (Blueprint $table) {
            //
        });
    }
}
