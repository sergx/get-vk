<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVkGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vk_groups', function (Blueprint $table) {
            $table->integer('id')->unsigned();
            $table->string('name', 256)->nullable();
            $table->string('screen_name', 256)->nullable();
            $table->integer('users_count')->unsigned()->nullable();
            $table->integer('users_parsed')->unsigned()->nullable();
            $table->tinyInteger('sort_type')->unsigned()->nullable();
            $table->string('type', 16)->nullable();
            $table->boolean('is_closed')->nullable();
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
        Schema::dropIfExists('vk_groups');
    }
}
