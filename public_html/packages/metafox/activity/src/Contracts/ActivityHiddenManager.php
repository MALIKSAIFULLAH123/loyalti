<?php

namespace MetaFox\Activity\Contracts;

use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\User;

/**
 * Interface ActivityHiddenManager.
 */
interface ActivityHiddenManager
{
    public function getCacheName(int $userId): string;

    public function clearCache(int $userId): void;

    public function isHide(User $user, Feed $feed): bool;

    /**
     * Get hidden feeds based on user.
     *
     * @param User $user
     *
     * @return array<int, mixed>
     */
    public function getHiddenFeeds(User $user): array;
}
