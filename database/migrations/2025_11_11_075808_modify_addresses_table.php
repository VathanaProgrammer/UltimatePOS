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
        Schema::table('api_user_addresses', function (Blueprint $table) {
            //
            if (Schema::hasColumn('api_user_addresses', 'country_code')) {
                $table->dropColumn('country_code');
            }

            if (Schema::hasColumn('api_user_addresses', 'house_number')) {
                $table->dropColumn('house_number');
            }

            if (Schema::hasColumn('api_user_addresses', 'road')) {
                $table->dropColumn('road');
            }

            if (Schema::hasColumn('api_user_addresses', 'neighbourhood')) {
                $table->dropColumn('neighbourhood');
            }

            if (Schema::hasColumn('api_user_addresses', 'village')) {
                $table->dropColumn('village');
            }

            if (Schema::hasColumn('api_user_addresses', 'town')) {
                $table->dropColumn('town');
            }

            if (Schema::hasColumn('api_user_addresses', 'city')) {
                $table->dropColumn('city');
            }

            if (Schema::hasColumn('api_user_addresses', 'state')) {
                $table->dropColumn('state');
            }

            if (Schema::hasColumn('api_user_addresses', 'postcode')) {
                $table->dropColumn('postcode');
            }

            if (Schema::hasColumn('api_user_addresses', 'country')) {
                $table->dropColumn('country');
            }

            // Add new columns only if they do NOT exist
            if (!Schema::hasColumn('api_user_addresses', 'phone')) {
                Schema::table('api_user_addresses', function (Blueprint $table) {
                    $table->string('phone')->nullable(); // use string instead of integer for phone
                });
            }

            if (!Schema::hasColumn('api_user_addresses', 'detail')) {
                Schema::table('api_user_addresses', function (Blueprint $table) {
                    $table->text('details')->nullable();
                });
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
        Schema::table('api_user_addresses', function (Blueprint $table) {
            //
        });
    }
};