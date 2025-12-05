<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            // Rename old collector_id to keep data safe
            if (Schema::hasColumn('c_customers', 'collector_id')) {
                $table->renameColumn('collector_id', 'old_collector_id');
            }

            // Add new collector_id referencing users.id
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

            if (Schema::hasColumn('c_customers', 'old_collector_id')) {
                $table->renameColumn('old_collector_id', 'collector_id');
            }
        });
    }
};