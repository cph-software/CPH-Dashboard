<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreFailureCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_failure_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('failure_code');
            $table->string('failure_name')->nullable();
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->text('description')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('default_category', ['Scrap', 'Repair', 'Claim']);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_failure_codes');
    }
}
