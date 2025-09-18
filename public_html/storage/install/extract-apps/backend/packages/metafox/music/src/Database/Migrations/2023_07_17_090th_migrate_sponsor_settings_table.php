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
        $this->removeDeprecatedSettings();
        $this->removeDeprecatedMenuItems();
    }

    protected function removeDeprecatedMenuItems(): void
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'music')
            ->whereIn('menu', ['music.music_playlist.itemActionMenu', 'music.music_playlist.detailActionMenu'])
            ->whereIn('name', ['unsponsor_in_feed', 'sponsor_in_feed'])
            ->delete();
    }

    protected function removeDeprecatedSettings(): void
    {
        $table = config('permission.table_names.permissions');

        if (!$table || !Schema::hasTable($table)) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['music', 'purchase_sponsor', \MetaFox\Music\Models\Playlist::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['music', 'purchase_sponsor_price', \MetaFox\Music\Models\Playlist::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['music', 'sponsor_in_feed', \MetaFox\Music\Models\Playlist::ENTITY_TYPE]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsor_settings');
    }
};
