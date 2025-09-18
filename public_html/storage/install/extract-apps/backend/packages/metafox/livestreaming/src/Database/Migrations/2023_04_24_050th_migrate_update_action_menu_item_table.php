<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;

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
        if (Schema::hasTable('update_action_menu_item')) {
            return;
        }

        // Update Menu on/off notifications
        $repository = resolve(MenuItemRepositoryInterface::class);
        $repository->getModel()
            ->newModelQuery()
            ->whereIn('menu', ['livestreaming.live_video.itemActionMenu', 'livestreaming.live_video.detailActionMenu'])
            ->where([
                ['resolution', '=', 'web'],
                ['label', '=', 'livestreaming::phrase.turn_off_notification'],
                ['icon', '=', 'ico-trash'],
            ])
            ->update([
                'icon'  => 'ico-bell2-off-o',
                'value' => 'livestreaming/offNotification',
                'label' => 'livestreaming::phrase.turn_off_notification',
            ]);
        $repository->getModel()
            ->newModelQuery()
            ->whereIn('menu', ['livestreaming.live_video.itemActionMenu', 'livestreaming.live_video.detailActionMenu'])
            ->where([
                ['resolution', '=', 'web'],
                ['label', '=', 'livestreaming::phrase.turn_on_notification'],
                ['icon', '=', 'ico-trash'],
            ])
            ->update([
                'icon'  => 'ico-bell2-o',
                'value' => 'livestreaming/onNotification',
                'label' => 'livestreaming::phrase.turn_on_notification',
            ]);

        // Update notification type
        $repository = resolve(TypeRepositoryInterface::class);
        $repository->getModel()
            ->newModelQuery()
            ->where([
                ['module_id', '=', 'livestreaming'],
                ['type', '=', 'start_livestream'],
                ['title', '=', 'livestreaming::phrase.start_livestream'],
            ])
            ->update([
                'channels' => ['database', 'mail', 'sms', 'mobilepush', 'webpush'],
                'title'    => 'livestreaming::phrase.friend_creates_a_new_live_video',
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
