<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('online_orders', 'address_type')) {
                $table->string('address_type')->default('current');
            }

            if (!Schema::hasColumn('online_orders', 'current_house_number')) {
                $table->string('current_house_number')->nullable();
            }

            if (!Schema::hasColumn('online_orders', 'current_road')) {
                $table->string('current_road')->nullable();
            }

            if (!Schema::hasColumn('online_orders', 'current_neighbourhood')) {
                $table->string('current_neighbourhood')->nullable();
            }

            if (!Schema::hasColumn('online_orders', 'current_village')) {
                $table->string('current_village')->nullable();
            }

            if (!Schema::hasColumn('online_orders', 'current_town')) {
                $table->string('current_town')->nullable();
            }

            if (!Schema::hasColumn('online_orders', 'current_city')) {
                $table->string('current_city')->nullable();
            }

            if (!Schema::hasColumn('online_orders', 'current_state')) {
                $table->string('current_state')->default('');
            }

            if (!Schema::hasColumn('online_orders', 'current_postcode')) {
                $table->string('current_postcode')->nullable();
            }

            if (!Schema::hasColumn('online_orders', 'current_country')) {
                $table->string('current_country')->default('Cambodia');
            }

            if (!Schema::hasColumn('online_orders', 'current_country_code')) {
                $table->string('current_country_code')->default('KH');
            }
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropColumn([
                'address_type',
                'current_house_number',
                'current_road',
                'current_neighbourhood',
                'current_village',
                'current_town',
                'current_city',
                'current_state',
                'current_postcode',
                'current_country',
                'current_country_code',
            ]);
        });
    }
};
