<?php

namespace MetaFox\Forum\Support\Browse\Traits\Moderate;

use MetaFox\Forum\Repositories\ModeratorRepositoryInterface;

trait ModeratorPermissionTrait
{
    public function hasAccess(int $userId, int|null $forumId, string $permission): bool
    {
        return resolve(ModeratorRepositoryInterface::class)->hasAccess($userId, $forumId, $permission);
    }
}
