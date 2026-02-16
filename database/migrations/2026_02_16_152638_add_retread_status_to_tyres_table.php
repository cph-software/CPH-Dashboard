<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRetreadStatusToTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify the enum to add 'Retread' status
        DB::statement("ALTER TABLE tyres MODIFY COLUMN status ENUM('Installed', 'New', 'Scrap', 'Repaired', 'Retread') DEFAULT 'New'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE tyres MODIFY COLUMN status ENUM('Installed', 'New', 'Scrap', 'Repaired') DEFAULT 'New'");
    }
}
