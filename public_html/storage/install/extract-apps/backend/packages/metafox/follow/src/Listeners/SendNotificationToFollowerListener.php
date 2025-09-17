<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Follow\Jobs\SendFollowerNotification;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;

/**
 * Class SendNotificationToFollowerListener.
 * @ignore
 */
class SendNotificationToFollowerListener
{
    /**
     * @param  User  $owner
     * @param  Model $model
     * @return void
     */
    public function handle(User $owner, Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        if ($model->owner instanceof HasPrivacyMember) {
            return;
        }

        SendFollowerNotification::dispatch($model);
    }
}
