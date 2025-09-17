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
        $this->createVideosTable();
        DbTableHelper::textTable('video_text');
        DbTableHelper::categoryTable('video_categories', true);
        DbTableHelper::createTagDataTable('video_tag_data');
        DbTableHelper::categoryDataTable('video_category_data');
        DbTableHelper::streamTables('video');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
        Schema::dropIfExists('video_categories');
        Schema::dropIfExists('video_tag_data');
        Schema::dropIfExists('video_category_data');
        Schema::dropIfExists('video_text');
        DbTableHelper::dropStreamTables('video');
    }

    private function createVideosTable()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedTinyInteger('in_process')->default(0);

            $table->unsignedTinyInteger('is_stream')->default(0);
            $table->unsignedTinyInteger('is_spotlight')->default(0);
            $table->unsignedBigInteger('group_id')->default(0);
            $table->unsignedBigInteger('album_id')->default(0);
            $table->unsignedInteger('album_type')->default(0);

            DbTableHelper::featuredColumn($table);
            DbTableHelper::sponsorColumn($table);
            DbTableHelper::approvedColumn($table);

            DbTableHelper::setupResourceColumns($table, true, true, true, true);

            DbTableHelper::imageColumns($table);
            DbTableHelper::imageColumns($table, 'thumbnail_file_id');
            DbTableHelper::imageColumns($table, 'video_file_id');

            $table->string('title', 255)->nullable(false);

            $table->string('destination', 255)->nullable();

            $table->string('asset_id', 255)->nullable();

            $table->string('video_url', 255)->nullable();

            $table->mediumText('embed_code')->nullable();

            DbTableHelper::feedContentColumn($table);

            $table->string('file_ext', 10)->nullable();

            DbTableHelper::totalColumns($table, ['like', 'comment', 'share', 'reply', 'view', 'rating', 'score']);

            $table->string('duration')->nullable();
            $table->string('resolution_x')->nullable();
            $table->string('resolution_y')->nullable();

            DbTableHelper::locationColumn($table);

            $table->timestamps();
        });
    }
};
