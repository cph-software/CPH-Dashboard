<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanMasterKaryawanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_master_karyawan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_id');
            $table->string('full_name');
            $table->unsignedBigInteger('job_position')->nullable()->index('karyawan_master_karyawan_job_position_foreign');
            $table->string('job_level');
            $table->date('join_date');
            $table->date('resign_date')->nullable();
            $table->string('employee_status')->nullable();
            $table->date('end_date')->nullable();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('citizen_id_address')->nullable();
            $table->string('resindetial_address')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('ptkp_status')->nullable();
            $table->string('employee_tax_status')->nullable();
            $table->string('tax-config')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('bpjs_ketenagakerjaan')->nullable();
            $table->string('bpjs_kesehatan')->nullable();
            $table->string('citizen_id_number')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('phone')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('religion')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('karyawan_master_karyawan');
    }
}
