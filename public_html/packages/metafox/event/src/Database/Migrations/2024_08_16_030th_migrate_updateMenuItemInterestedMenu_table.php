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
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'event.event.interestedMenu',
            'name' => 'interested',
        ])->update([
            'ordering' => 0,
        ]);

        \MetaFox\Menu\Models\Menu::query()->where([
            'name'          => 'event.event.goingMenu',
            'resource_name' => 'event',
            'resolution'    => 'mobile',
        ])->delete();

        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'event.event.goingMenu',
            'name' => 'going',
        ])->delete();

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
