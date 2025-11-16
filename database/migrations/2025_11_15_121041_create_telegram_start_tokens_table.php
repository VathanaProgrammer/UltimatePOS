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
        Schema::create('telegram_start_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 100)->unique();
            $table->unsignedInteger('order_online_id')->nullable();
            $table->foreign('order_online_id')
                  ->references('id')
                  ->on('online_orders')
                  ->onDelete('cascade');
            
            $table->unsignedInteger('api_user_id')->nullable();
            $table->foreign('api_user_id')
                  ->references('id')
                  ->on('api_users')
                  ->onDelete('cascade');
                  
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at')->nullable();
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
        Schema::dropIfExists('telegram_start_tokens');
    }
};