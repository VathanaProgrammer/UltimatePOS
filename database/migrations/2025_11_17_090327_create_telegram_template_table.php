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
        Schema::create('telegram_templates', function (Blueprint $table) {
            $table->increments('id');
            
            $table->unsignedInteger('business_id')->nullable();
            $table->foreign('business_id')
                    ->references('id')
                    ->on('business')
                    ->onDelete('SET NULL');

            $table->string('name');
            $table->text('greeting');
            $table->text('body');
            $table->text('footer');
            $table->boolean('auto_send')->default(1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_templates');
    }
};