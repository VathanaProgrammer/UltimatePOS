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
            if (Schema::hasColumn('online_orders', 'current_house_number')) {
                $table->dropColumn('current_house_number');
            }
            if (Schema::hasColumn('online_orders', 'current_road')) {
                $table->dropColumn('current_road');
            }
            if (Schema::hasColumn('online_orders', 'current_neighbourhood')) {
                $table->dropColumn('current_neighbourhood');
            }
            if (Schema::hasColumn('online_orders', 'current_village')) {
                $table->dropColumn('current_village');
            }
            if (Schema::hasColumn('online_orders', 'current_town')) {
                $table->dropColumn('current_town');
            }
            if (Schema::hasColumn('online_orders', 'current_city')) {
                $table->dropColumn('current_city');
            }
            if (Schema::hasColumn('online_orders', 'current_state')) {
                $table->dropColumn('current_state');
            }
            if (Schema::hasColumn('online_orders', 'current_postcode')) {
                $table->dropColumn('current_postcode');
            }
            if (Schema::hasColumn('online_orders', 'current_country')) {
                $table->dropColumn('current_country');
            }
            if (Schema::hasColumn('online_orders', 'current_country_code')) {
                $table->dropColumn('current_country_code');
            }

            if (!Schema::hasColumn('online_orders', 'current_address_id')) {
                $table->unsignedInteger('current_address_id')->nullable()->default(null)->after('id');
                $table->foreign('current_address_id')
                    ->references('id')
                    ->on('api_current_user_addresses')
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
            //
        });
    }
};
