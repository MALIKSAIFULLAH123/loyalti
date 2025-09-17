<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Platform\Contracts\User;

/**
 * Class PointUpdatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PointUpdatedListener
{
    /**
     * @param  User|null            $context
     * @param  User|null            $owner
     * @param  int                  $type
     * @param  int                  $amount
     * @param  array<string, mixed> $extra
     * @return void
     */
    public function handle(?User $context, ?User $owner, int $type, int $amount, array $extra = []): void
    {
        if (!$context) {
            return;
        }

        // Update point statistic
        ActivityPoint::updateStatistic($context, $type, $amount);
    }
}
