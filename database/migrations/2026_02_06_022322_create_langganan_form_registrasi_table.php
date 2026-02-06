<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanggananFormRegistrasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('langganan_form_registrasi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transid');
            $table->string('password');
            $table->string('pemilik_nama');
            $table->string('pemilik_sebagai');
            $table->string('pemilik_alamat');
            $table->string('pemilik_kota');
            $table->string('pemilik_provinsi');
            $table->string('pemilik_kodepos')->nullable();
            $table->string('pemilik_no_telepon');
            $table->string('pemilik_no_whatsapp');
            $table->string('pemilik_email')->nullable();
            $table->string('pemilik_no_ktp');
            $table->string('pemilik_foto_ktp');
            $table->string('usaha_nama');
            $table->string('usaha_alamat');
            $table->string('usaha_kota')->nullable();
            $table->string('usaha_provinsi')->nullable();
            $table->string('usaha_kodepos')->nullable();
            $table->string('usaha_no_telepon')->nullable();
            $table->string('usaha_jenis_usaha');
            $table->string('usaha_website')->nullable();
            $table->string('usaha_akta_pendirian')->nullable();
            $table->string('usaha_foto_akta_pendirian')->nullable();
            $table->date('usaha_tanggal_akta_pendirian')->nullable();
            $table->string('usaha_npwp')->nullable();
            $table->string('usaha_foto_npwp')->nullable();
            $table->date('usaha_tanggal_npwp')->nullable();
            $table->string('usaha_siup')->nullable();
            $table->string('usaha_foto_siup')->nullable();
            $table->date('usaha_tanggal_siup')->nullable();
            $table->string('usaha_luas_tanah')->nullable();
            $table->string('usaha_luas_bangunan')->nullable();
            $table->date('usaha_berdiri_sejak')->nullable();
            $table->string('usaha_status')->nullable();
            $table->string('usaha_link_foto_tempat_usaha');
            $table->string('usaha_link_lokasi_peta');
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
        Schema::dropIfExists('langganan_form_registrasi');
    }
}
