<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Menu\Models\MenuItem;

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
        MenuItem::query()
            ->where([
                'name' => 'music',
            ])
            ->whereIn('menu', ['music.music_song.headerItemActionOnGroupProfileMenu',
                'music.music_song.headerItemActionOnPageProfileMenu',
                'music.music_song.headerItemActionOnUserProfileMenu'])
            ->update([
                'label' => 'music::phrase.add_new_music',
            ]);

        MenuItem::query()
            ->where([
                'menu' => 'music.sidebarMenu',
                'name' => 'add_song',
            ])
            ->update([
                'label' => 'music::phrase.add_new_music',
            ]);
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
