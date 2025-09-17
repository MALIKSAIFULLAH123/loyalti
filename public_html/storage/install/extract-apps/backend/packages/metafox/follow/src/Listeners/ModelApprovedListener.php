<?php

namespace MetaFox\Follow\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Follow\Jobs\SendFollowerNotification;
use MetaFox\Follow\Policies\FollowPolicy;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        if (!policy_check(FollowPolicy::class, 'notifyFollowers', $context, $model)) {
            return;
        }

        SendFollowerNotification::dispatch($model);
    }
}
