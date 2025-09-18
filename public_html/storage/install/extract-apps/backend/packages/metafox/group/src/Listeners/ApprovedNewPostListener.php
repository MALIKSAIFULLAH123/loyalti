<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Jobs\SendFollowerNotification;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class ApprovedNewPostListener.
 *
 * @ignore
 * @deprecated
 */
class ApprovedNewPostListener
{
    /**
     * @param Content $model
     * @param User    $resource
     *
     * @return void
     */
    public function handle(Content $model, User $resource): void
    {
        if (!$resource instanceof Group) {
            return;
        }

        if (!$model->isApproved()) {
            return;
        }

        if (method_exists($model->item, 'toFollowerNotification')) {
            return;
        }

        SendFollowerNotification::dispatch($model, $resource);
    }
}
