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
        $this->updateMenuItemIcons();
        $this->updateMenuItemOrderings();
    }

    protected function updateMenuItemOrderings(): void
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'photo')
            ->whereIn('menu', ['photo.photo.itemActionMenu', 'photo.photo.detailActionMenu'])
            ->whereIn('name', ['sponsor_in_feed', 'purchase_sponsor_in_feed', 'remove_sponsor_in_feed'])
            ->update([
                'ordering' => 8,
            ]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'photo')
            ->whereIn('menu', ['photo.photo.itemActionMenu', 'photo.photo.detailActionMenu'])
            ->whereIn('name', ['sponsor', 'remove_sponsor', 'purchase_sponsor'])
            ->update([
                'ordering' => 11,
            ]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'photo')
            ->whereIn('menu', ['photo.photo_album.itemActionMenu', 'photo.photo_album.detailActionMenu'])
            ->whereIn('name', ['sponsor_in_feed', 'purchase_sponsor_in_feed', 'remove_sponsor_in_feed'])
            ->update([
                'ordering' => 2,
            ]);

        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'photo')
            ->whereIn('menu', ['photo.photo_album.itemActionMenu', 'photo.photo_album.detailActionMenu'])
            ->whereIn('name', ['sponsor', 'remove_sponsor', 'purchase_sponsor'])
            ->update([
                'ordering' => 3,
            ]);
    }

    protected function updateMenuItemIcons(): void
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'photo')
            ->whereIn('menu', ['photo.photo.itemActionMenu', 'photo.photo.detailActionMenu', 'photo.photo_album.itemActionMenu', 'photo.photo_album.detailActionMenu'])
            ->whereIn('name', ['sponsor_in_feed', 'sponsor'])
            ->update([
                'icon' => 'ico-sponsor',
            ]);
    }

    protected function removeDeprecatedMenuItems(): void
    {
        \MetaFox\Menu\Models\MenuItem::query()
            ->where('module_id', 'photo')
            ->whereIn('menu', ['photo.photo.itemActionMenu', 'photo.photo.detailActionMenu', 'photo.photo_album.itemActionMenu', 'photo.photo_album.detailActionMenu'])
            ->whereIn('name', ['unsponsor', 'unsponsor_in_feed'])
            ->delete();
    }

    protected function removeDeprecatedSettings(): void
    {
        $table = config('permission.table_names.permissions');

        if (!$table || !Schema::hasTable($table)) {
            return;
        }

        app('events')->dispatch('authorization.permission.delete', ['photo', 'purchase_sponsor', \MetaFox\Photo\Models\Photo::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['photo', 'purchase_sponsor_price', \MetaFox\Photo\Models\Photo::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['photo_album', 'purchase_sponsor', \MetaFox\Photo\Models\Album::ENTITY_TYPE]);
        app('events')->dispatch('authorization.permission.delete', ['photo_album', 'purchase_sponsor_price', \MetaFox\Photo\Models\Album::ENTITY_TYPE]);
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
