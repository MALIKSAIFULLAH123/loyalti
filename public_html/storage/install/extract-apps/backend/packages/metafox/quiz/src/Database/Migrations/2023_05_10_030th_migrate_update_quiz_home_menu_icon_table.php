<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
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
        $repository->getModel()
            ->newModelQuery()
            ->where([
                ['menu', '=', 'core.dropdownMenu'],
                ['name', '=', 'quiz'],
                ['resolution', '=', 'web'],
                ['icon', '=', 'ico-check-square-o3'],
            ])
            ->update(['icon' => 'ico-question-mark']);

        $repository->getModel()
            ->newModelQuery()
            ->where([
                ['menu', '=', 'core.primaryMenu'],
                ['name', '=', 'quiz'],
                ['resolution', '=', 'web'],
                ['icon', '=', 'ico-check-square-o3'],
            ])
            ->update(['icon' => 'ico-question-mark']);

        $repository->getModel()
            ->newModelQuery()
            ->where([
                ['menu', '=', 'core.bodyMenu'],
                ['name', '=', 'quizzes'],
                ['resolution', '=', 'mobile'],
                ['icon', '=', 'check-square-o3'],
            ])
            ->update(['icon' => 'question-mark']);
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
