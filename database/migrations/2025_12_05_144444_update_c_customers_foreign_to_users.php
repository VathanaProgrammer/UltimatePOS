<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            // Drop old foreign key if it exists
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('c_customers');
            if ($doctrineTable->hasForeignKey('c_customers_collector_id_foreign')) {
                $table->dropForeign('c_customers_collector_id_foreign');
            }

            // Drop old column entirely
            if (Schema::hasColumn('c_customers', 'collector_id')) {
                $table->dropColumn('collector_id');
            }

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
            // Drop foreign key to users
            $table->dropForeign(['collector_id']);
            $table->dropColumn('collector_id');

            // Optional: add old collector column back
            $table->unsignedInteger('collector_id')->nullable()->after('id');
        });
    }
};