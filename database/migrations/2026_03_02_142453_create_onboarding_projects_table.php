<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnboardingProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onboarding_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('customer_name');
            $table->string('site_name')->nullable();
            $table->enum('status', ['Prospect', 'Data Collection', 'Validation', 'Training', 'Go-Live'])->default('Prospect');
            $table->integer('progress_percent')->default(0);
            
            // Kuesioner Data (Sheet 5)
            $table->json('questionnaire_answers')->nullable();
            
            // PIC Data (Sheet 2)
            $table->json('pics_data')->nullable(); // Nama, Title, Email, WA
            
            // Files Tracker
            $table->json('uploaded_files')->nullable();
            
            $table->unsignedBigInteger('internal_pic_id')->nullable(); // Relasi ke User CPH
            $table->text('internal_notes')->nullable();
            
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key (optional, based on your User table)
            // $table->foreign('internal_pic_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onboarding_projects');
    }
}
