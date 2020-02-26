<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVkUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // Тут все возможные данные по пользователю:
    // https://vk.com/dev/users.get?params[user_ids]=210700286&params[fields]=photo_id%2C%20verified%2C%20sex%2C%20bdate%2C%20city%2C%20country%2C%20home_town%2C%20has_photo%2C%20photo_50%2C%20photo_100%2C%20photo_200_orig%2C%20photo_200%2C%20photo_400_orig%2C%20photo_max%2C%20photo_max_orig%2C%20online%2C%20domain%2C%20has_mobile%2C%20contacts%2C%20site%2C%20education%2C%20universities%2C%20schools%2C%20status%2C%20last_seen%2C%20followers_count%2C%20common_count%2C%20occupation%2C%20nickname%2C%20relatives%2C%20relation%2C%20personal%2C%20connections%2C%20exports%2C%20activities%2C%20interests%2C%20music%2C%20movies%2C%20tv%2C%20books%2C%20games%2C%20about%2C%20quotes%2C%20can_post%2C%20can_see_all_posts%2C%20can_see_audio%2C%20can_write_private_message%2C%20can_send_friend_request%2C%20is_favorite%2C%20is_hidden_from_feed%2C%20timezone%2C%20screen_name%2C%20maiden_name%2C%20crop_photo%2C%20is_friend%2C%20friend_status%2C%20career%2C%20military%2C%20blacklisted%2C%20blacklisted_by_me%2C%20can_be_invited_group&params[name_case]=Nom&params[v]=5.103
    public function up()
    {
        Schema::create('vk_users', function (Blueprint $table) {
            $table->integer('id')->unique();
            //$table->boolean('is_closed')->nullable();
            //$table->boolean('has_photo')->nullable();
            //$table->boolean('can_see_all_posts')->nullable();
            
            $table->integer('last_seen_days')->nullable();

            $table->integer('first_name_id')->nullable();
            $table->integer('last_name_id')->nullable();
            $table->tinyInteger('sex')->nullable();
            $table->integer('bdate_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('country_id')->nullable();
            $table->string('domain', 140)->nullable();

            
            $table->text('site')->nullable();
            //$table->string('university_name', 140)->nullable();
            //$table->string('faculty_name', 140)->nullable();
            
            $table->string('status', 140)->nullable();

            $table->integer('followers_count')->nullable();
            
            $table->string('mobile_phone', 32)->nullable();

            $table->boolean('verified')->nullable();
            $table->tinyInteger('deactivated')->nullable();
            $table->boolean('can_access_closed')->nullable();
            $table->boolean('can_write_private_message')->nullable();
            $table->integer('univ_fast_string_id')->nullable();
            $table->integer('parsed_date_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vk_users');
    }
}
