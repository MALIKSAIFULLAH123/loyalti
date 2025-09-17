<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        \MetaFox\Menu\Models\MenuItem::query()->where('name', 'feature')
            ->whereIn('menu', ['music.music_playlist.itemActionMenu', 'music.music_playlist.detailActionMenu'])
            ->delete();
        \MetaFox\Menu\Models\MenuItem::query()->where('name', 'unfeature')
            ->whereIn('menu', ['music.music_playlist.itemActionMenu', 'music.music_playlist.detailActionMenu'])
            ->delete();
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
