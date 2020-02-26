<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('url')->nullable();
            $table->string('method', 256)->nullable();
            $table->text('params')->nullable();
            $table->integer('result_code')->unsigned()->nullable();
            $table->integer('result_length')->unsigned()->nullable();
            $table->string('cache_key', 128)->nullable();
            $table->text('result')->nullable();
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
        Schema::dropIfExists('request_histories');
    }
}
