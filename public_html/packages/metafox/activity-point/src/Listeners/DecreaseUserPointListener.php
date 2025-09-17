<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\ActivityPoint\Support\ActivityPoint as Support;

class DecreaseUserPointListener
{
    public function handle(?User $user, ?Entity $content, string $action): int
    {
        if (null === $user) {
            return 0;
        }

        if (null === $content) {
            return 0;
        }

        return ActivityPoint::updateUserPoints($user, $content, $action, Support::TYPE_RETRIEVED);
    }
}
