<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            // Drop old foreign key if it exists
            $table->dropForeign(['collector_id']);

            // Make sure type matches users.id (bigInteger if needed)
            $table->unsignedInteger('collector_id')->nullable()->change();

            // Add new foreign key to users table
            $table->foreign('collector_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            // Remove foreign key to users
            $table->dropForeign(['collector_id']);
        });
    }
};