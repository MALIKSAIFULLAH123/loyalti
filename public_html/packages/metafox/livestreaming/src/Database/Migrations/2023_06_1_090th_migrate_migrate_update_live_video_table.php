<?php

use Illuminate\Database\Migrations\Migration;
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
        $repository = resolve(MenuItemRepositoryInterface::class);
        $repository->getModel()
            ->newModelQuery()
            ->whereIn('menu', ['livestreaming.live_video.itemActionMenu', 'livestreaming.live_video.detailActionMenu'])
            ->where([
                ['resolution', '=', 'web'],
                ['value', '=', 'deleteItem'],
                ['ordering', '=', 13],
            ])
            ->update([
                'ordering' => 18,
            ]);

        $repository->getModel()
            ->newModelQuery()
            ->whereIn('menu', ['livestreaming.live_video.itemActionMenu', 'livestreaming.live_video.detailActionMenu'])
            ->where([
                ['resolution', '=', 'mobile'],
                ['value', '=', 'deleteItem'],
                ['ordering', '=', 11],
            ])
            ->update([
                'ordering' => 17,
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
