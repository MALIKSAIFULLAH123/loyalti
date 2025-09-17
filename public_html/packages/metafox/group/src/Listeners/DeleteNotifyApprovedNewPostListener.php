<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Notifications\ApproveNewPostNotification;
use MetaFox\Platform\Contracts\Content;

/**
 * Class DeleteNotifyApprovedNewPostListener.
 * @ignore
 */
class DeleteNotifyApprovedNewPostListener
{
    /**
     * @param  Model $model
     * @return void
     */
    public function handle(Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        $type = (new ApproveNewPostNotification())->getType();

        if ($model->owner instanceof Group) {
            app('events')->dispatch(
                'notification.delete_notification_by_type_and_item',
                [$type, $model->entityId(), $model->entityType()]
            );
        }
    }
}
