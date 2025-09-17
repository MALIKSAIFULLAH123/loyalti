<?php

use MetaFox\Photo\Models\Album;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

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
        if (!Schema::hasTable('photo_albums')) {
            return;
        }

        Album::withoutEvents(function () {
            Album::query()
                ->where('album_type', Album::TIMELINE_ALBUM)
                ->where('owner_type', 'user')
                ->where('user_type', 'user')
                ->whereColumn('user_id', '!=', 'owner_id')
                ->update(['user_id' => \DB::raw('owner_id')]);
        });

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
