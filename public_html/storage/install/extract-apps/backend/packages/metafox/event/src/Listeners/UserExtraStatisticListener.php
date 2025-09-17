<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserExtraStatisticListener
{
    public function handle(?User $context, ?User $user, string $itemType, int $itemId): ?array
    {
        if ($itemType != Event::ENTITY_TYPE) {
            return null;
        }

        $userStatistics = resolve(EventRepositoryInterface::class)->getUserExtraStatistics($context, $user, $itemId);

        return [
            Event::ENTITY_TYPE => $userStatistics,
        ];
    }
}
