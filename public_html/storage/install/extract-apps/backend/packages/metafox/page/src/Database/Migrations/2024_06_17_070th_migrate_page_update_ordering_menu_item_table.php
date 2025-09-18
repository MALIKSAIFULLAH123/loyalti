<?php

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
        $updates = [
            [
                'name'     => 'page_info',
                'ordering' => 1,
            ],
            [
                'name'     => 'about_page',
                'ordering' => 2,
            ],
            [
                'name'     => 'invite_friends',
                'ordering' => 3,
            ],
            [
                'name'     => 'add_new_admin',
                'ordering' => 4,
            ],
            [
                'name'     => 'schedule_post',
                'ordering' => 5,
            ],
            [
                'name'     => 'permissions',
                'ordering' => 8,
            ],
            [
                'name'     => 'menu',
                'ordering' => 11,
            ],
        ];
        $deletes = [
            [
                'showWhen'   => [
                    'and',
                    ['falsy', 'item.is_pending'],
                ],
                'menu'       => 'page.manageMenu',
                'name'       => 'edit_avatar',
                'label'      => 'core::web.edit_avatar',
                'ordering'   => 3,
                'value'      => 'page/editItemAvatar',
                'icon'       => 'camera-o',
                'is_deleted' => 1,
            ],
            [
                'showWhen'   => [
                    'and',
                    ['truthy', 'item.extra.can_add_cover'],
                ],
                'menu'       => 'page.manageMenu',
                'name'       => 'edit_cover',
                'label'      => 'core::web.edit_cover',
                'ordering'   => 4,
                'value'      => 'page/editItemCover',
                'icon'       => 'camera-o',
                'is_deleted' => 1,
            ],
            [
                'showWhen'   => [
                    'and',
                    ['truthy', 'item.extra.can_add_cover'],
                ],
                'menu'       => 'page.page.profileActionMenu',
                'name'       => 'update_cover',
                'label'      => 'page::phrase.update_cover',
                'ordering'   => 1,
                'value'      => '@app/EDIT_ITEM_COVER',
                'icon'       => '',
                'is_deleted' => 1,
            ],
            [
                'showWhen'   => [
                    'and',
                    ['truthy', 'item.extra.can_upload_avatar'],
                ],
                'menu'       => 'page.page.profileActionMenu',
                'name'       => 'update_avatar',
                'label'      => 'page::phrase.update_avatar',
                'ordering'   => 2,
                'value'      => '@app/EDIT_ITEM_AVATAR',
                'icon'       => '',
                'is_deleted' => 1,
            ],
        ];

        foreach ($updates as $update) {
            \MetaFox\Menu\Models\MenuItem::query()
                ->where('resolution', 'mobile')
                ->where('name', $update['name'])
                ->where('menu', 'page.manageMenu')
                ->update([
                    'ordering' => $update['ordering'],
                ]);
        }

        \MetaFox\Menu\Models\MenuItem::query()
            ->where('resolution', 'mobile')
            ->whereIn('name', [
                'edit_avatar', 'edit_cover',
            ])
            ->where('menu', 'page.manageMenu')
            ->delete();


        \MetaFox\Menu\Models\MenuItem::query()
            ->where('resolution', 'mobile')
            ->whereIn('name', [
                'update_avatar', 'update_cover',
            ])
            ->where('menu', 'page.page.profileActionMenu')
            ->delete();
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('update_ordering_menu_item');
    }
};
