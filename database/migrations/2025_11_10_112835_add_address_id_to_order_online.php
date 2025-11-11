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
        Schema::table('online_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('online_orders', 'saved_address_id')) {
                $table->unsignedInteger('saved_address_id')->nullable()->after('id'); // allow NULL for current addresses
                $table->foreign('saved_address_id')
                      ->references('id')
                      ->on('api_user_addresses')
                      ->onDelete('SET NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (Schema::hasColumn('online_orders', 'saved_address_id')) {
                $table->dropForeign(['saved_address_id']);
                $table->dropColumn('saved_address_id');
            }
        });
    }
};
