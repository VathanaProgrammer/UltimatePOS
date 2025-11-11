<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('api_users', function (Blueprint $table) {
            // Remove columns that are not needed
            if (Schema::hasColumn('api_users', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('api_users', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('api_users', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('api_users', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('api_users', 'phone')) {
                $table->dropColumn('phone');
            }

            // Add link to contacts
            if (!Schema::hasColumn('api_users', 'contact_id')) {
                $table->unsignedInteger('contact_id')->unique()->after('id');
                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('api_users', function (Blueprint $table) {
            // Reverse changes if needed
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
        });
    }
};
