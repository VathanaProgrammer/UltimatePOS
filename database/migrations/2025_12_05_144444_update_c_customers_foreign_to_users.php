<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop tables if they exist to start fresh
        Schema::dropIfExists('c_photos');
        Schema::dropIfExists('c_customers');

        // Create c_customers table
        Schema::create('c_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('address_detail')->nullable();
            $table->unsignedInteger('collector_id')->nullable();
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('collector_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Create c_photos table
        Schema::create('c_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->string('image_url');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('c_customers')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('c_photos');
        Schema::dropIfExists('c_customers');
    }
};