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
        Schema::table('c_customers', function (Blueprint $table) {
            //
            $table->decimal('latitude', 10, 7)->nullable()->after('phone');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('address_detail')->nullable()->after('longitude');

            $table->unsignedInteger('collector_id')->nullable()->after('id');
            $table->foreign('collector_id')
                ->references('id')
                ->on('collector')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address_detail']);
        });
    }
};