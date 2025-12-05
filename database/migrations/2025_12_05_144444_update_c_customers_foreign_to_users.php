<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop tables if they exist
        Schema::dropIfExists('c_photos');
        Schema::dropIfExists('c_customers');
        Schema::dropIfExists('collector');

        // Create c_customers table
        Schema::create('c_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('address_detail')->nullable();

            $table->unsignedBigInteger('collector_id')->nullable()->after('id');
            $table->foreign('collector_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->timestamps();
        });

        // Create c_photos table
        Schema::create('c_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('image_url');
            $table->timestamps();

            $table->foreign('customer_id')
                  ->references('id')
                  ->on('c_customers')
                  ->onDelete('cascade');
        });

        // Create collector table (optional if still needed, otherwise skip)
        Schema::create('collector', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('status')->default('active');
            $table->string('role')->default("collector");
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('c_photos');
        Schema::dropIfExists('c_customers');
        Schema::dropIfExists('collector');
    }
};