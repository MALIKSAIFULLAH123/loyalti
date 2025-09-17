<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Platform\Contracts\User;

class DecreaseUserPointWithCustomActionListener
{
    public function handle(?User $user, int $points, string $action, string $actionTypeName, array $actionParams = []): bool
    {
        if (null === $user) {
            return false;
        }

        if ($points <= 0) {
            return false;
        }

        return ActivityPoint::decreaseUserPointsWithAction($user, $points, $action, $actionTypeName, $actionParams);
    }
}
