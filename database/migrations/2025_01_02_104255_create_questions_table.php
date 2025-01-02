<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quiz_units', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('quiz_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('quiz_units')->onDelete('cascade');
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_id')->constrained('quiz_details')->onDelete('cascade');
            $table->text('text')->nullable();
            $table->json('answer')->nullable(); // Changed from string to json
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quiz_details');
        Schema::dropIfExists('quiz_units');
    }
};
