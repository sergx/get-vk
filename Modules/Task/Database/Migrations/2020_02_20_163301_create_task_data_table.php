<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer("task_id")->nullable();
            $table->string("key", 128)->nullable();
            $table->longText('value')->nullable();
            $table->string('file',128)->nullable();
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
        Schema::dropIfExists('task_data');
    }
}
