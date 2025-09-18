<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('music_song_tag_data')) {
            DbTableHelper::createTagDataTable('music_song_tag_data');
        }

        if (!Schema::hasTable('music_album_tag_data')) {
            DbTableHelper::createTagDataTable('music_album_tag_data');
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('music_song_tag_data');
        Schema::dropIfExists('music_album_tag_data');
    }
};
