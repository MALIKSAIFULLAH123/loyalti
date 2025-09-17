<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

class IncreaseUserPointListener
{
    public function handle(?User $user, Entity $content, string $action, bool $allowMultiTransactionsPerItem = false): int
    {
        return ActivityPoint::updateUserPoints($user, $content, $action, \MetaFox\ActivityPoint\Support\ActivityPoint::TYPE_EARNED, $allowMultiTransactionsPerItem);
    }
}
