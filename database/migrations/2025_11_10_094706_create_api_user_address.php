<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_user_addresses', function (Blueprint $table) {
            $table->increments('id');

            // Link to user
            $table->unsignedInteger('api_user_id');
            $table->foreign('api_user_id')
                  ->references('id')
                  ->on('api_users')
                  ->onDelete('cascade');

            // Full structured address
            $table->string('label')->nullable();               // Home, Work, etc.
            $table->string('house_number')->nullable();        // e.g., 45
            $table->string('road')->nullable();                // e.g., Street 1928
            $table->string('neighbourhood')->nullable();      // e.g., Phum Bayab
            $table->string('village')->nullable();            // e.g., Sangkat Phnom Penh Thmei
            $table->string('town')->nullable();               // e.g., Khan Sen Sok
            $table->string('city')->nullable();               // Normalized city
            $table->string('state');              // Province, e.g., Phnom Penh
            $table->string('postcode')->nullable();           // e.g., 120801
            $table->string('country')->default('Cambodia');
            $table->string('country_code')->default('KH');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('api_user_addresses', function (Blueprint $table) {
            $table->dropForeign(['api_user_id']);
        });

        Schema::dropIfExists('api_user_addresses');
    }
};
