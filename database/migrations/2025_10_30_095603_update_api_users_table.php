<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('api_users', function (Blueprint $table) {
            // Add username if it doesn't exist yet
            if (!Schema::hasColumn('api_users', 'username')) {
                $table->string('username')->unique()->after('id');
            }

            // Remove columns safely
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

            // Ensure phone and profile_url exist
            if (!Schema::hasColumn('api_users', 'phone')) {
                $table->string('phone')->nullable()->after('username');
            }
            if (!Schema::hasColumn('api_users', 'profile_url')) {
                $table->string('profile_url')->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_users', function (Blueprint $table) {
            // Re-add removed columns
            if (!Schema::hasColumn('api_users', 'first_name')) {
                $table->string('first_name')->after('id');
            }
            if (!Schema::hasColumn('api_users', 'last_name')) {
                $table->string('last_name')->after('first_name');
            }
            if (!Schema::hasColumn('api_users', 'email')) {
                $table->string('email')->unique()->after('last_name');
            }
            if (!Schema::hasColumn('api_users', 'password')) {
                $table->string('password')->after('email');
            }
        });
    }
};
