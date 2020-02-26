<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVkGroupVkUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vk_group_vk_user', function (Blueprint $table) {
            $table->integer('vk_group_id')->unsigned()->nullable();
            $table->integer('vk_user_id')->unsigned()->nullable();
            $table->unique(['vk_group_id', 'vk_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vk_group_vk_user');
    }
}
