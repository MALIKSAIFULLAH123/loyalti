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
        $this->updateMenuForWeb();
        $this->updateMenuForMobile();

        // to do here
    }

    private function updateMenuForWeb(): void
    {
        $menus = [
            [
                'name'     => 'manager_group',
                'ordering' => 1,
            ],
            [
                'name'     => 'pending_posts',
                'ordering' => 2,
            ],
            [
                'name'     => 'membership_questions',
                'ordering' => 3,
            ],
            [
                'name'     => 'membership_requests',
                'ordering' => 4,
            ],
            [
                'name'     => 'group_rules',
                'ordering' => 5,
            ],
            [
                'name'     => 'member-reported_content',
                'ordering' => 6,
            ],
            [
                'name'     => 'moderation_rights',
                'ordering' => 7,
            ],
            [
                'name'     => 'settings',
                'ordering' => 8,
            ],
        ];

        foreach ($menus as $menu) {
            \MetaFox\Menu\Models\MenuItem::query()
                ->where([
                    'menu'       => 'group.groupManagerMenu',
                    'name'       => $menu['name'],
                    'resolution' => 'web',
                ])
                ->update(['ordering' => $menu['ordering']]);
        }
    }

    private function updateMenuForMobile(): void
    {
        $menus = [
            [
                'name'     => 'group_info',
                'ordering' => 1,
            ],
            [
                'name'     => 'about_group',
                'ordering' => 2,
            ],
            [
                'name'     => 'privacy',
                'ordering' => 3,
            ],
            [
                'name'     => 'pending_posts',
                'ordering' => 4,
            ],
            [
                'name'     => 'membership_questions',
                'ordering' => 5,
            ],
            [
                'name'     => 'membership_requests',
                'ordering' => 6,
            ],
            [
                'name'     => 'group_rules',
                'ordering' => 7,
            ],
            [
                'name'     => 'moderation_rights',
                'ordering' => 8,
            ],
            [
                'name'     => 'permissions',
                'ordering' => 9,
            ],
            [
                'name'     => 'member_reported_content',
                'ordering' => 10,
            ],
            [
                'name'     => 'add_new_admin',
                'ordering' => 11,
            ],
            [
                'name'     => 'add_new_moderate',
                'ordering' => 12,
            ],
            [
                'name'     => 'menu',
                'ordering' => 13,
            ],
        ];

        foreach ($menus as $menu) {
            \MetaFox\Menu\Models\MenuItem::query()
                ->where([
                    'menu'       => 'group.manageMenu',
                    'name'       => $menu['name'],
                    'resolution' => 'mobile',
                ])
                ->update(['ordering' => $menu['ordering']]);
        }
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
