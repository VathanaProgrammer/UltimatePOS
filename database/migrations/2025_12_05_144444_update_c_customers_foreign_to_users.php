<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop collector table
        Schema::dropIfExists('collector');

        // Update c_customers table to reference users instead
        Schema::table('c_customers', function (Blueprint $table) {
            // Drop old foreign key if exists
            $table->dropForeign(['collector_id']);
            
            // Drop old collector_id column
            $table->dropColumn('collector_id');

            // Add new column referencing users table
            $table->unsignedBigInteger('collector_id')->nullable()->after('id');
            $table->foreign('collector_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        // Revert c_customers changes
        Schema::table('c_customers', function (Blueprint $table) {
            $table->dropForeign(['collector_id']);
            $table->dropColumn('collector_id');
        });

        // Recreate collector table
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
};