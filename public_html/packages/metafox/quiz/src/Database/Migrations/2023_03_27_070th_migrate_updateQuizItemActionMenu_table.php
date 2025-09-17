<?php

use MetaFox\Menu\Models\MenuItem;
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
        MenuItem::query()->getModel()
            ->where([
                'menu'       => 'quiz.quiz.itemActionMenu',
                'resolution' => 'mobile',
                'name'       => 'save',
            ])
            ->update([
                'value' => 'saveItemDetail',
            ]);

        MenuItem::query()->getModel()
            ->where([
                'menu'       => 'quiz.quiz.itemActionMenu',
                'resolution' => 'mobile',
                'name'       => 'un-save',
            ])
            ->update([
                'value' => 'undoSaveItemDetail',
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
