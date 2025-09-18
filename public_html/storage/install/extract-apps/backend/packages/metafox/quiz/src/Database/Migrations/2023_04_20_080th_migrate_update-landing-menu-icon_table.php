<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;

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

        $repository = resolve(MenuItemRepositoryInterface::class);
        $menuItem   = $repository->getModel()
            ->newModelQuery()
            ->where([
                ['menu', '=', 'quiz.sidebarMenu'],
                ['name', '=', 'landing'],
                ['resolution', '=', 'web'],
                ['icon', '=', 'ico-newspaper-alt-o'],
            ])
            ->first();

        if ($menuItem instanceof MenuItem) {
            $menuItem->update(['icon' => 'ico-question-mark']);
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
