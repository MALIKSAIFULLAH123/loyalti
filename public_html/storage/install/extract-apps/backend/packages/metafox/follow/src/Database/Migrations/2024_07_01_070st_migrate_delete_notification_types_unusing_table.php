<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Notification\Models\ModuleSetting;
use MetaFox\Notification\Models\NotificationModule;
use MetaFox\Notification\Models\NotificationSetting;

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
        $data = NotificationSetting::query()
            ->select('notification_settings.*')
            ->leftJoin('notification_types', 'notification_types.id', '=', 'notification_settings.type_id')
            ->where('notification_types.type', 'follower')
            ->where('notification_settings.user_value', '0')
            ->get();

        if ($data->isEmpty()) {
            return;
        }

        NotificationModule::query()
            ->where('module_id', 'follow')
            ->each(function (NotificationModule $item) use ($data) {

                $data = $data->where('channel', $item->channel);
                if ($data->isEmpty()) {
                    return;
                }

                foreach ($data as $value) {
                    ModuleSetting::query()->updateOrCreate([
                        'user_id'   => $value['user_id'],
                        'user_type' => $value['user_type'],
                        'module_id' => $item->entityId(),
                    ], [
                        'user_id'    => $value['user_id'],
                        'user_type'  => $value['user_type'],
                        'module_id'  => $item->entityId(),
                        'user_value' => 0,
                    ]);
                }
            });

        $types = ['follower'];
        app('events')->dispatch('notification.delete_types', [$types]);

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
