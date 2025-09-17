<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\Platform\Contracts\User;

class GetPointStatisticsListener
{
    public function handle(?User $user): ?PointStatistic
    {
        if (!$user instanceof User) {
            return null;
        }

        return PointStatistic::query()->find($user->entityId());
    }
}
