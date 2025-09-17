<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class ActivityCheckSpamStatusListener
{
    public function checkSpamStatus(User $user, string $itemType, ?string $content, ?int $itemId = null): bool
    {
        return resolve(FeedRepositoryInterface::class)->checkSpamStatus($user, $itemType, $content, $itemId);
    }
}
