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
        if (!Schema::hasTable('photo_albums')) {
            return;
        }

        $this->removePhrases();
        $this->removeMenuItems();
        $this->removePermissions();
    }

    private function removePhrases(): void
    {
        if (!Schema::hasTable('phrases')) {
            return;
        }

        \MetaFox\Localize\Models\Phrase::query()
            ->whereIn('key', [
                'photo::permission.can_sponsor_in_feed_photo_album_label',
                'photo::permission.can_sponsor_in_feed_photo_album_desc'
            ])
            ->where([
                'package_id' => 'metafox/photo'
            ])
            ->delete();
    }

    private function removeMenuItems(): void
    {
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        $names = ['sponsor_in_feed', 'remove_sponsor_in_feed', 'purchase_sponsor_in_feed'];

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'package_id' => 'metafox/photo',
                'menu'       => 'photo.photo_album.itemActionMenu'
            ])
            ->whereIn('name', $names)
            ->delete();

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'package_id' => 'metafox/photo',
                'menu'       => 'photo.photo_album.detailActionMenu'
            ])
            ->whereIn('name', $names)
            ->delete();
    }

    private function removePermissions(): void
    {
        if (!Schema::hasTable('auth_permissions')) {
            return;
        }

        /**
         * @var \MetaFox\Authorization\Models\Permission $permission
         */
        $permission = \MetaFox\Authorization\Models\Permission::query()
            ->where([
                'module_id' => 'photo',
                'name'      => 'photo_album.sponsor_in_feed'
            ])
            ->first();

        if (null === $permission) {
            return;
        }

        $permission->delete();

        if (!Schema::hasTable('auth_role_has_permissions')) {
            return;
        }

        $permission->rolesHasPermissions()->sync([]);
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
