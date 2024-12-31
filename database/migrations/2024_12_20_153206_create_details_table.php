<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id'); // Foreign key to units table
            $table->string('title')->nullable();
            $table->string('section')->nullable();
            $table->text('content')->nullable();
            $table->text('example')->nullable();
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('details');
    }

};
