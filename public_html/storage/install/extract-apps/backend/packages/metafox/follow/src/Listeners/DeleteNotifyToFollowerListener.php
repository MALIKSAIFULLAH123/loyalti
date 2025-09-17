<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;

/**
 * Class DeleteNotifyToFollowerListener.
 *
 * @ignore
 */
class DeleteNotifyToFollowerListener
{
    /**
     * @param Model $model
     *
     * @return void
     */
    public function handle(Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        if ($model->owner instanceof HasPrivacyMember) {
            return;
        }

        if (!method_exists($model, 'toFollowerNotification')) {
            return;
        }

        $type = Arr::get($model->toFollowerNotification(), 'type');

        if (empty($type)) {
            return;
        }

        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            [$type, $model->entityId(), $model->entityType()]
        );
    }
}
