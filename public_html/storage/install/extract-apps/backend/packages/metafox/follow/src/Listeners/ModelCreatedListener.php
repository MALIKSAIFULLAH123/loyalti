<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Follow\Jobs\SendFollowerNotification;
use MetaFox\Follow\Policies\FollowPolicy;
use MetaFox\Platform\Contracts\Content;

/**
 * Class ModelCreatedListener.
 * @ignore
 */
class ModelCreatedListener
{
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        if (!policy_check(FollowPolicy::class, 'notifyFollowers', $model->user, $model)) {
            return;
        }

        SendFollowerNotification::dispatch($model);
    }
}
