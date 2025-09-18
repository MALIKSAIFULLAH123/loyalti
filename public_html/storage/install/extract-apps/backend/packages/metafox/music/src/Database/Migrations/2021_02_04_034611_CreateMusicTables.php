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
        //
        if (!Schema::hasTable('music_albums')) {
            Schema::create('music_albums', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::viewColumn($table);
                DbTableHelper::privacyColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);

                DbTableHelper::imageColumns($table);
                DbTableHelper::moduleColumn($table);
                DbTableHelper::totalColumns($table, ['track', 'play', 'like', 'comment', 'reply', 'rating', 'length']);

                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);

                $table->string('name');

                $table->string('year', 10)
                    ->nullable();

                $table->decimal('total_score', 4, 1)->default(0.00);

                $table->unsignedTinyInteger('album_type')->default(0);

                $table->timestamps();
            });
        }

        DbTableHelper::textTable('music_album_text');

        if (!Schema::hasTable('music_genres')) {
            Schema::create('music_genres', function (Blueprint $table) {
                $table->increments('id');

                $table->string('name');

                DbTableHelper::totalColumns($table, ['album', 'track', 'playlist']);

                $table->unsignedInteger('ordering')->default(0);

                $table->boolean('is_active')->default(1);
            });
        }

        if (!Schema::hasTable('music_playlists')) {
            Schema::create('music_playlists', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);

                DbTableHelper::totalColumns($table, ['track', 'length', 'like', 'comment', 'reply', 'share', 'view', 'play']);

                DbTableHelper::imageColumns($table);

                $table->string('name', 100);

                $table->mediumText('description');

                $table->unsignedInteger('ordering')->default(0);

                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('music_songs')) {
            Schema::create('music_songs', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::viewColumn($table);

                $table->boolean('explicit')
                    ->default(0);

                DbTableHelper::imageColumns($table, 'song_file_id');

                $table->unsignedInteger('genre_id')
                    ->nullable();

                $table->unsignedBigInteger('album_id')
                    ->nullable();

                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::moduleColumn($table);
                DbTableHelper::privacyColumn($table);
                DbTableHelper::totalColumns(
                    $table,
                    ['track', 'length', 'like', 'comment', 'reply', 'share', 'view', 'play', 'score', 'rating']
                );
                $table->unsignedInteger('duration')
                    ->default(0);

                DbTableHelper::imageColumns($table);
                $table->string('name', 100);

                $table->mediumText('description');

                $table->unsignedInteger('ordering')
                    ->default(0);

                $table->timestamps();
            });
        }

        DbTableHelper::categoryDataTable('music_genre_data', 'genre_id');

        if (!Schema::hasTable('music_playlist_data')) {
            Schema::create('music_playlist_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('playlist_id');
                $table->unsignedInteger('ordering');
                $table->unique(['item_id', 'playlist_id']);
                $table->timestamps();
            });
        }

        DbTableHelper::streamTables('music_album');
        DbTableHelper::streamTables('music_playlist');
        DbTableHelper::streamTables('music_song');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('music_songs');
        Schema::dropIfExists('music_playlists');
        Schema::dropIfExists('music_album');
        Schema::dropIfExists('music_playlist_data');
        Schema::dropIfExists('music_genres');
        Schema::dropIfExists('music_genre_data');
        Schema::dropIfExists('music_album_text');
        Schema::dropIfExists('music_albums');
        DbTableHelper::dropStreamTables('music_album');
        DbTableHelper::dropStreamTables('music_playlist');
        DbTableHelper::dropStreamTables('music_song');
    }
};
