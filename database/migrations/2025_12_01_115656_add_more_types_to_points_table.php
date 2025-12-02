<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Modify ENUM column to include new types
        DB::statement("ALTER TABLE reward_point_histories MODIFY COLUMN type ENUM('earn', 'redeem', 'expire', 'bonus', 'adjustment') NOT NULL");
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Rollback ENUM column to previous state
        DB::statement("ALTER TABLE reward_point_histories MODIFY COLUMN type ENUM('earn', 'redeem', 'expire') NOT NULL");
    }
};