<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBdateAndUnivFastToVkUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vk_users', function (Blueprint $table) {
            $table->string('bdate', 12)->after('bdate_id')->nullable();
            $table->text('univ_fast_string')->after('univ_fast_string_id')->nullable();
            $table->bigInteger('last_seen')->after('last_seen_days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vk_users', function (Blueprint $table) {
            $table->dropColumn('bdate');
            $table->dropColumn('univ_fast_string');
            $table->dropColumn('last_seen');
        });
    }
}
