<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->increments('id');

            // FK to users table
            $table->unsignedInteger('api_user_id');
            $table->foreign('api_user_id')
                  ->references('id')
                  ->on('api_users')
                  ->onDelete('cascade');
            $table->decimal('total');
            $table->decimal('total_qty');
            $table->string('stauts')->default('ordered');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropForeign(['api_user_id']);
        });

        Schema::dropIfExists('online_orders');
    }
};
