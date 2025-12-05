<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    public function up()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            // Drop old foreign key if exists
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('c_customers');
            if (isset($indexes['collector_id_foreign'])) {
                $table->dropForeign(['collector_id']);
            }

            // Only modify foreign key without adding column again
            $table->unsignedBigInteger('collector_id')->nullable()->change();

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
        });
    }
};