<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreCompanyMappingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyre_company_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tyre_company_id')->constrained('tyre_companies')->onDelete('cascade');
            $table->foreignId('tyre_brand_id')->constrained('tyre_brands')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('tyre_company_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tyre_company_id')->constrained('tyre_companies')->onDelete('cascade');
            $table->foreignId('tyre_pattern_id')->constrained('tyre_patterns')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('tyre_company_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tyre_company_id')->constrained('tyre_companies')->onDelete('cascade');
            $table->foreignId('tyre_size_id')->constrained('tyre_sizes')->onDelete('cascade');
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
        Schema::dropIfExists('tyre_company_sizes');
        Schema::dropIfExists('tyre_company_patterns');
        Schema::dropIfExists('tyre_company_brands');
    }
}
