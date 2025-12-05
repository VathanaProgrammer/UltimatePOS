<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            // Add new collector_id referencing users.id
            if (!Schema::hasColumn('c_customers', 'collector_id_new')) {
                $table->unsignedBigInteger('collector_id_new')->nullable()->after('id');
                $table->foreign('collector_id_new')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });

        // Optional: copy old collector_id values to new column if needed
        \DB::table('c_customers')->update([
            'collector_id_new' => \DB::raw('collector_id')
        ]);

        Schema::table('c_customers', function (Blueprint $table) {
            // Drop old column safely
            $table->dropColumn('collector_id');
        });

        // Rename new column to original name
        Schema::table('c_customers', function (Blueprint $table) {
            $table->renameColumn('collector_id_new', 'collector_id');
        });
    }

    public function down()
    {
        Schema::table('c_customers', function (Blueprint $table) {
            $table->dropForeign(['collector_id']);
            $table->dropColumn('collector_id');
        });
    }
};