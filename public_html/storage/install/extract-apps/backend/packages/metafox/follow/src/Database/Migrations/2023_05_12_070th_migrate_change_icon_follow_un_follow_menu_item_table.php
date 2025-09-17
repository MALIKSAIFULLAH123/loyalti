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
        $menus = ['follow' => 'ico-user2-plus-o', 'unfollow' => 'ico-user2-minus-o'];

        foreach ($menus as $name => $icon) {
            MenuItem::query()
                ->where([
                    'resolution' => 'web',
                    'menu'       => 'user.user.profileActionMenu',
                    'name'       => $name,
                ])
                ->update([
                    'icon' => $icon,
                ]);
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
