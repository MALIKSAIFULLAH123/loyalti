<?php

use Illuminate\Database\Migrations\Migration;

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
                'name'     => 'group_info',
                'ordering' => 2,
            ],
            [
                'name'     => 'about_group',
                'ordering' => 3,
            ],
            [
                'name'     => 'privacy',
                'ordering' => 4,
            ],
            [
                'name'     => 'pending_posts',
                'ordering' => 6,
            ],
            [
                'name'     => 'membership_questions',
                'ordering' => 7,
            ],
            [
                'name'     => 'membership_requests',
                'ordering' => 8,
            ],
            [
                'name'     => 'group_rules',
                'ordering' => 9,
            ],
            [
                'name'     => 'moderation_rights',
                'ordering' => 11,
            ],
            [
                'name'     => 'add_new_admin',
                'ordering' => 12,
            ],
            [
                'name'     => 'add_new_moderate',
                'ordering' => 13,
            ],
            [
                'name'     => 'permissions',
                'ordering' => 17,
            ],
            [
                'name'     => 'menu',
                'ordering' => 21,
            ],
        ];

        foreach ($updates as $update) {
            \MetaFox\Menu\Models\MenuItem::query()
                ->where('name', $update['name'])
                ->where('menu', 'group.manageMenu')
                ->where('resolution', 'mobile')
                ->update(['ordering' => $update['ordering']]);
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
    }
};
