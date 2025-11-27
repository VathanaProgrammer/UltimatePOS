<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->foreign('customer_id')
                ->references('id')
                ->on('c-customers')
                ->onDelete('cascade');
                
            $table->string('image_url'); // store filename or path
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('c_photos');
    }
};