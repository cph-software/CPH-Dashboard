<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandAndStatusToTyrePatterns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyre_patterns', function (Blueprint $table) {
            $table->unsignedBigInteger('tyre_brand_id')->nullable()->after('id');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('name');
            
            $table->foreign('tyre_brand_id')->references('id')->on('tyre_brands')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyre_patterns', function (Blueprint $table) {
            $table->dropForeign(['tyre_brand_id']);
            $table->dropColumn(['tyre_brand_id', 'status']);
        });
    }
}
