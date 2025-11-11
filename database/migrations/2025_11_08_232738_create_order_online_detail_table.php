<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_online_details', function (Blueprint $table) {
            $table->increments('id');

            // order_online_id FK
            $table->unsignedInteger('order_online_id');
            $table->foreign('order_online_id')
                ->references('id')
                ->on('online_orders')
                ->onDelete('cascade');

            // product_id FK
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on("products")
                ->onDelete('cascade');

            $table->decimal('qty', 8, 2)->default(1.00);
            $table->decimal('price_at_order', 10, 2);
            $table->decimal('total_line', 10, 2);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_online_details');
    }
};
