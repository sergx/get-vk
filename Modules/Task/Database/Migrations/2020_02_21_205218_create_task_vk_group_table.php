<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskVkGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_vk_group', function (Blueprint $table) {
            $table->bigInteger('vk_group_id');
            $table->bigInteger('task_id')->unsigned()->nullable();
            $table->integer('sort_order')->nullable();
            $table->unique(['vk_group_id', 'task_id']);
        });

        Schema::table('task_vk_group', function (Blueprint $table) {
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_vk_group');
    }
}
