<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        MenuItem::query()
            ->where('package_id', 'metafox/subscription')
            ->where('resolution', 'web')
            ->where('menu', 'subscription.sidebarMenu')
            ->where('name', 'my')
            ->update([
                'icon'       => 'ico-address-book-o',
                'ordering'   => 1,
                'to'         => '/subscription',
                'extra->tab' => 'landing',
            ]);

        MenuItem::query()
            ->where('package_id', 'metafox/subscription')
            ->where('resolution', 'web')
            ->where('menu', 'subscription.sidebarMenu')
            ->where('name', 'landing')
            ->update([
                'ordering'   => 2,
                'to'         => '/subscription/package',
                'extra->tab' => 'package',
            ]);

        MenuItem::query()
            ->where('package_id', 'metafox/subscription')
            ->where('resolution', 'mobile')
            ->where('menu', 'subscription.sidebarMenu')
            ->where('name', 'landing')
            ->update([
                'ordering' => 3,
            ]);

        MenuItem::query()
            ->where('package_id', 'metafox/subscription')
            ->where('resolution', 'web')
            ->where('menu', 'core.primaryMenu')
            ->where('name', 'subscription')
            ->update([
                'icon' => 'ico-address-book',
            ]);
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
