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
        $menuItem = new \MetaFox\Menu\Models\MenuItem();

        $menuItem->newModelQuery()->where('module_id', 'group')
            ->where('menu', 'group.group.profileActionMenu')
            ->where('name', 'your_content')
            ->delete();

        $menuItem->newModelQuery()->where('module_id', 'group')
            ->where('menu', 'group.groupManagerMenu')
            ->where('name', 'pending_posts')
            ->delete();

        $menuItem->newModelQuery()->where('module_id', 'group')
            ->where('menu', 'group.manageMenu')
            ->where('name', 'pending_posts')
            ->delete();

        $menuItem->newModelQuery()->where('module_id', 'group')
            ->where('menu', 'group.creatorContentMenu')
            ->whereIn('name', ['removed', 'declined', 'published', 'pending'])
            ->delete();
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
