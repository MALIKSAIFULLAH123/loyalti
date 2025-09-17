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
        // update menu item for mobile
        MenuItem::query()
            ->where([
                'menu'       => 'core.helperMenu',
                'name'       => 'contact',
                'resolution' => 'mobile',
            ])
            ->update(['label' => 'contact::phrase.contact_us']);

        MenuItem::query()
            ->where([
                'menu'       => 'core.helperMenu',
                'name'       => 'terms_and_policy',
                'resolution' => 'mobile',
            ])
            ->update(['icon' => 'merge-file']);

        MenuItem::query()
            ->where([
                'menu'       => 'core.bodyMenu',
                'name'       => 'saved_items',
                'resolution' => 'mobile',
            ])
            ->update(['icon' => 'bookmark']);

        MenuItem::query()
            ->where([
                'menu'       => 'core.bodyMenu',
                'name'       => 'video',
                'resolution' => 'mobile',
            ])
            ->update(['icon' => 'video-player']);

        MenuItem::query()
            ->where([
                'menu'       => 'core.bodyMenu',
                'name'       => 'activity_point',
                'resolution' => 'mobile',
            ])
            ->update(['icon' => 'star-circle']);

        // update menu item for web
        MenuItem::query()
            ->where([
                'menu'       => 'core.dropdownMenu',
                'name'       => 'saved',
                'resolution' => 'web',
            ])
            ->update(['icon' => 'ico-bookmark']);

        MenuItem::query()
            ->where([
                'menu'       => 'core.primaryMenu',
                'name'       => 'saved',
                'resolution' => 'web',
            ])
            ->update(['icon' => 'ico-bookmark']);

        MenuItem::query()
            ->where([
                'menu'       => 'core.dropdownMenu',
                'name'       => 'activitypoint',
                'resolution' => 'web',
            ])
            ->update(['icon' => 'ico-star-circle']);

        MenuItem::query()
            ->where([
                'menu'       => 'core.accountMenu',
                'name'       => 'activity_points',
                'resolution' => 'web',
            ])
            ->update(['icon' => 'ico-star-circle']);

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
