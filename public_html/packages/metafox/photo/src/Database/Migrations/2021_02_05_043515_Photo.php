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
        $this->createPhotoAlbumTable();

        $this->createAlbumInForTable();

        $this->createAlbumItemTable();

        $this->createPhotoTable();

        $this->createPhotoGroupTable();

        $this->createPhotoGroupItemTable();

        $this->createPhotoInfoTable();

        DbTableHelper::categoryTable('photo_categories', true);
        DbTableHelper::categoryDataTable('photo_category_data');

        DbTableHelper::streamTables('photo');
        DbTableHelper::streamTables('photo_album');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photos');
        Schema::dropIfExists('photo_albums');
        Schema::dropIfExists('photo_album_info');
        Schema::dropIfExists('photo_album_item');
        Schema::dropIfExists('photo_groups');
        Schema::dropIfExists('photo_categories');
        Schema::dropIfExists('photo_category_data');
        Schema::dropIfExists('photo_info');
        DbTableHelper::dropStreamTables('photo');
        DbTableHelper::dropStreamTables('photo_album');
    }

    public function createPhotoAlbumTable(): void
    {
        if (!Schema::hasTable('photo_albums')) {
            Schema::create('photo_albums', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::moduleColumn($table);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::privacyColumn($table);
                $table->string('name');

                DbTableHelper::approvedColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);

                $table->unsignedInteger('cover_photo_id')
                    ->default(0)->index();

                $table->unsignedInteger('album_type')->default(0);
                DbTableHelper::totalColumns($table, ['view', 'photo', 'item', 'comment', 'reply', 'like', 'share']);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function createAlbumInForTable(): void
    {
        if (!Schema::hasTable('photo_album_info')) {
            Schema::create('photo_album_info', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->mediumText('description')->nullable();
            });
        }
    }

    public function createAlbumItemTable(): void
    {
        if (!Schema::hasTable('photo_album_item')) {
            Schema::create('photo_album_item', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('album_id');
                DbTableHelper::morphColumn($table, 'item');
                $table->unsignedInteger('ordering')->default(0);
                $table->timestamps();
            });
        }
    }

    public function createPhotoTable(): void
    {
        if (!Schema::hasTable('photos')) {
            Schema::create('photos', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('album_id')
                    ->default(0)->index();

                $table->unsignedInteger('album_type')->default(0);

                $table->unsignedBigInteger('group_id')
                    ->default(0);

                $table->tinyInteger('type_id')
                    ->default(0);

                $table->string('title');

                $table->string('item_type')->default('photo');

                DbTableHelper::imageColumns($table);

                DbTableHelper::setupResourceColumns($table, true, true, true, true);

                DbTableHelper::totalColumns($table, ['view', 'like', 'dislike', 'comment', 'reply', 'share', 'tag', 'download', 'vote']);

                $table->float('total_rating', 3, 2)
                    ->default('0.00');

                $table->unsignedTinyInteger('mature')
                    ->default(0);

                $table->tinyInteger('allow_rate')
                    ->default(0);

                DbTableHelper::approvedColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);
                DbTableHelper::locationColumn($table);
                DbTableHelper::feedContentColumn($table);

                $table->tinyInteger('is_temp')
                    ->default(0);

                $table->unsignedInteger('ordering')
                    ->default(0);

                $table->timestamps();

                $table->softDeletes();
            });
        }
    }

    public function createPhotoGroupTable(): void
    {
        if (!Schema::hasTable('photo_groups')) {
            Schema::create('photo_groups', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('album_id')
                    ->default(0)->index();

                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::privacyColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);
                DbTableHelper::locationColumn($table);
                DbTableHelper::feedContentColumn($table);
                DbTableHelper::approvedColumn($table);

                DbTableHelper::totalColumns($table, ['photo', 'item', 'view', 'like', 'comment', 'reply', 'share']);

                $table->timestamps();
            });
        }
    }

    public function createPhotoGroupItemTable(): void
    {
        if (!Schema::hasTable('photo_group_items')) {
            Schema::create('photo_group_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('group_id');
                DbTableHelper::morphColumn($table, 'item');
                $table->unsignedInteger('ordering')->default(0);
                $table->timestamps();
            });
        }
    }

    public function createPhotoInfoTable()
    {
        if (!Schema::hasTable('photo_info')) {
            Schema::create('photo_info', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                DbTableHelper::resourceTextColumns($table);
            });
        }
    }
};
