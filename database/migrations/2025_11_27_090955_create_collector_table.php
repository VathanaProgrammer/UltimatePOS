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
        Schema::create('collector', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('phone')->unique();
            $table->string('password')->unique();
            $table->string('status')->default('active');
            $table->string('role')->default("collector");
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
        Schema::dropIfExists('collector');
    }
};