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
        Schema::create('reward_point_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->unsignedInteger('api_user_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();
            $table->unsignedInteger('online_order_id')->nullable();

            $table->integer('points');
            $table->enum('type', ['earn', 'redeem', 'expire']);
            $table->string('description', 255)->nullable();

            $table->timestamps();

            $table->index('contact_id', 'contact_id_index');
            $table->index('transaction_id', 'transaction_id_index');
            $table->index('online_order_id', 'online_order_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reward_point_histories');
    }
};