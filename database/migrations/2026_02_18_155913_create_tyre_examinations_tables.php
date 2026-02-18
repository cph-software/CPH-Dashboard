<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreExaminationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add missing fields to tyre_sizes for Point 1
        if (!Schema::hasColumn('tyre_sizes', 'rim_size')) {
            Schema::table('tyre_sizes', function (Blueprint $table) {
                $table->string('rim_size')->nullable()->after('ply_rating');
            });
        }

        // 2. Create tyre_examinations (Header)
        Schema::create('tyre_examinations', function (Blueprint $table) {
            $table->id();
            $table->date('examination_date');
            $table->string('location')->nullable();
            $table->decimal('odometer', 15, 2)->nullable();
            $table->decimal('hour_meter', 15, 2)->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('driver_1')->nullable();
            $table->string('driver_2')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('tyre_man')->nullable();
            $table->string('ka_kendaraan')->nullable();
            $table->string('logistics')->nullable();
            $table->string('verified_by')->nullable();
            $table->string('plant_manager')->nullable();
            $table->enum('status', ['Draft', 'Verified', 'Approved'])->default('Draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('master_import_kendaraan')->onDelete('cascade');
        });

        // 3. Create tyre_examination_details (Detail)
        Schema::create('tyre_examination_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examination_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('tyre_id'); // Tyre that was installed at inspection time
            $table->decimal('psi_reading', 8, 2)->nullable();
            $table->decimal('rtd_1', 5, 2)->nullable();
            $table->decimal('rtd_2', 5, 2)->nullable();
            $table->decimal('rtd_3', 5, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('examination_id')->references('id')->on('tyre_examinations')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('tyre_position_details')->onDelete('cascade');
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyre_examination_details');
        Schema::dropIfExists('tyre_examinations');

        if (Schema::hasColumn('tyre_sizes', 'rim_size')) {
            Schema::table('tyre_sizes', function (Blueprint $table) {
                $table->dropColumn('rim_size');
            });
        }
    }
}
