<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Notifications\ApproveNewPostNotification;
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

        if ($model->owner instanceof Page) {
            app('events')->dispatch(
                'notification.delete_notification_by_type_and_item',
                [$type, $model->entityId(), $model->entityType()]
            );
        }
    }
}
