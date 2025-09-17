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
        /*
         *
    [
        'menu'     => 'user.settingMenu',
        'name'     => 'divider',
        'ordering' => 20,
        'as'       => 'divider',
    ],
    [
        'menu'     => 'user.settingMenu',
        'name'     => 'profile_menu_settings',
        'label'    => 'user::phrase.profile_menu_settings',
        'ordering' => 21,
        'as'       => 'sidebarHeading',
    ],
    [
        'tab'      => 'profile-menu',
        'menu'     => 'user.settingMenu',
        'name'     => 'profile_menu',
        'label'    => 'user::phrase.menu',
        'ordering' => 22,
        'to'       => '/settings/profile-menu',
    ],
         */

        $update = [
            'blocked'               => [
                'ordering' => 18,
            ],
            'divider'               => [
                'ordering' => 20,
            ],
            'profile_menu_settings' => [
                'ordering' => 21,
            ],
            'profile_menu'          => [
                'ordering' => 22,
            ],
        ];

        foreach ($update as $key => $value) {
            \MetaFox\Menu\Models\MenuItem::query()
                ->where([
                    'menu' => 'user.settingMenu',
                    'name' => $key,
                ])->update($value);
        };

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {}
};
