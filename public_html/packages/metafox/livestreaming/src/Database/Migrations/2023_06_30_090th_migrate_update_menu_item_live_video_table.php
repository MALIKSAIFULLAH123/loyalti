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
            ->where([
                ['menu', '=', 'livestreaming.sidebarMenu'],
                ['resolution', '=', 'web'],
                ['label', '=', 'livestreaming::phrase.create_live_video'],
                ['ordering', '=', 8],
            ])
            ->update([
                'ordering' => 9,
            ]);
        $repository->getModel()
            ->newModelQuery()
            ->where([
                ['menu', '=', 'livestreaming.sidebarMenu'],
                ['resolution', '=', 'web'],
                ['label', '=', 'livestreaming::phrase.my_videos'],
                ['ordering', '=', 6],
            ])
            ->update([
                'ordering' => 5,
            ]);
        $repository->getModel()
            ->newModelQuery()
            ->where([
                ['menu', '=', 'livestreaming.sidebarMenu'],
                ['resolution', '=', 'mobile'],
                ['value', '=', 'viewFriendLiveVideos'],
                ['ordering', '=', 5],
            ])
            ->update([
                'ordering' => 6,
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
