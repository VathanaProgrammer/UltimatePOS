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
        Schema::create('api_current_user_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label')->nullable()->default('Current Address');
            $table->integer('phone');
            $table->text('details')->nullable();
            $table->json('coordinates');
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
        Schema::dropIfExists('api_current_user_addresses');
    }
};
