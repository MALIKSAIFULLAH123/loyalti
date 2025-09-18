<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Jobs\SendFollowerNotification;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class ApprovedNewPostListener.
 * @ignore
 * @deprecated
 */
class ApprovedNewPostListener
{
    /**
     * @param  Content $model
     * @param  User    $resource
     * @return void
     */
    public function handle(Content $model, User $resource): void
    {
        if (!$resource instanceof Page) {
            return;
        }

        if (!$model->isApproved()) {
            return;
        }

        if (method_exists($model->item, 'toFollowerNotification')) {
            return;
        }

        SendFollowerNotification::dispatch($model->item, $resource);
    }
}
