<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToImportBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('tyre_company_id')->nullable()->after('user_id');
            $table->foreign('tyre_company_id')->references('id')->on('tyre_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropForeign(['tyre_company_id']);
            $table->dropColumn('tyre_company_id');
        });
    }
}
