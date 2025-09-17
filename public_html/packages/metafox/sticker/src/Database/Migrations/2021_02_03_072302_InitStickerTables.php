<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stickers')) {
            Schema::create('stickers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('set_id');
                DbTableHelper::imageColumns($table);
                $table->string('image_path')->nullable();
                $table->string('server_id')->nullable();
                $table->unsignedInteger('ordering')
                    ->default(0);
                $table->unsignedTinyInteger('view_only')
                    ->default(0);
                $table->unsignedTinyInteger('is_deleted')
                    ->default(0);
            });
        }

        if (!Schema::hasTable('sticker_sets')) {
            Schema::create('sticker_sets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->unsignedInteger('used')
                    ->default(0);
                $table->unsignedBigInteger('total_sticker')
                    ->default(0);
                $table->unsignedTinyInteger('is_default')
                    ->default(0)->index();
                $table->unsignedTinyInteger('is_active')
                    ->default(1);
                $table->unsignedBigInteger('thumbnail_id')
                    ->default(0);
                $table->string('image_path')->nullable();
                $table->string('server_id')->nullable();
                $table->unsignedInteger('ordering')
                    ->default(0);
                $table->unsignedTinyInteger('view_only')
                    ->default(0);
                $table->unsignedTinyInteger('is_deleted')
                    ->default(0);
            });
        }

        if (!Schema::hasTable('sticker_user_values')) {
            Schema::create('sticker_user_values', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->unsignedBigInteger('set_id');
                $table->unique(['user_id', 'set_id']);
            });
        }

        // track recent-used sticker
        if (!Schema::hasTable('sticker_recent')) {
            Schema::create('sticker_recent', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->unsignedBigInteger('sticker_id');
                $table->timestamps();

                $table->unique(['user_id', 'sticker_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('sticker_recent');
        Schema::dropIfExists('sticker_user_values');
        Schema::dropIfExists('sticker_sets');
        Schema::dropIfExists('stickers');
    }
};
