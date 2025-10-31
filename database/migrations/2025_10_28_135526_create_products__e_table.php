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
        Schema::create('products_E', function (Blueprint $table) {
            $table->increments("id");
            
            // Link to existing POS products table
            $table->unsignedInteger('product_id')->unique(); // 32 bit unsigned integer
        
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unsignedInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories_E')->onDelete('cascade');

            // Foreign key constraint
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_E');
    }
};
