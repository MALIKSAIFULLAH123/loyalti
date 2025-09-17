<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @codeCoverageIgnore
 * @ignore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->string('phone_number')->nullable();
            $table->string('full_phone_number')->nullable();

            $table->tinyInteger('gender')->default(0);
            $table->mediumText('custom_gender')->nullable();

            $table->date('birthday')->nullable();
            $table->string('birthday_search')->nullable();
            $table->unsignedInteger('birthday_doy')->nullable();
            $table->string('country_iso')->nullable();
            $table->string('country_state_id')->default(0);
            $table->string('country_city_code')->default(0);
            $table->string('city_location')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('language_id')->nullable();
            $table->unsignedSmallInteger('style_id')->nullable();
            $table->unsignedInteger('timezone_id')->default(0);
            $table->string('currency_id', 3)->nullable();
            $table->tinyInteger('dst_check')->default(0);

            DbTableHelper::morphImage($table, 'avatar');
            DbTableHelper::morphImage($table, 'cover');
            $table->string('cover_photo_position', 10)->nullable();

            $table->tinyInteger('hide_tip')->nullable()->default(0);
            $table->string('status')->nullable();
            $table->tinyInteger('footer_bar')->nullable()->default(0);
            $table->unsignedBigInteger('invite_user_id')->nullable();

            $table->tinyInteger('im_beep')->default(0);
            $table->tinyInteger('im_hide')->default(0);
            $table->unsignedSmallInteger('total_spam')->default(0);

            $table->tinyInteger('previous_relation_type')->default(0);
            $table->unsignedBigInteger('previous_relation_with')->default(0);

            $table->tinyInteger('relation')->default(0);
            $table->unsignedBigInteger('relation_with')->default(0);

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
        Schema::dropIfExists('user_profiles');
    }
};
