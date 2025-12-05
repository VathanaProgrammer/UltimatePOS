<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['collector_id']);
            $table->dropColumn('collector_id'); // drop column to avoid ->change()

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
        Schema::table('c_customers', function (Blueprint $table) {
            $table->dropForeign(['collector_id']);
            $table->dropColumn('collector_id');
        });
    }
};