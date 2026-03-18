<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyreExaminationImagesTable extends Migration
{
    public function up()
    {
        Schema::create('tyre_examination_images', function (Blueprint $table) {
            $table->id('image_id');
            $table->unsignedBigInteger('examination_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('image_type');
            $table->string('image_path');
            $table->string('original_name')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tyre_examination_images');
    }
}
